<?php

namespace Atlas\Laravel\Tests\Services;

use Atlas\Laravel\Services\ModelService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Orchestra\Testbench\TestCase;

class ModelServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('widgets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('widget_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('widget_id')->constrained('widgets')->cascadeOnDelete();
            $table->string('content');
            $table->timestamps();
        });

        Schema::create('soft_widgets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function test_performs_basic_crud_operations(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);
        $this->assertInstanceOf(Widget::class, $widget);
        $this->assertSame('Alpha', $widget->name);
        $this->assertCount(1, $service->list());

        $found = $service->find($widget->id);
        $this->assertInstanceOf(Widget::class, $found);
        $this->assertSame('Alpha', $found->name);

        $service->update($widget, ['name' => 'Beta']);
        $this->assertSame('Beta', $service->find($widget->id)?->name);

        $service->delete($widget);
        $this->assertNull($service->find($widget->id));
    }

    public function test_list_paginated_and_build_query(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;

            public function buildQuery(array $options = []): Builder
            {
                return parent::buildQuery($options)
                    ->when($options['filters']['name'] ?? null, function ($q, $name) {
                        $q->where('name', $name);
                    })
                    ->when($options['search'] ?? false, function ($q, $search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            }
        };

        $service->create(['name' => 'Alpha']);
        $service->create(['name' => 'Beta']);
        $service->create(['name' => 'Gamma']);

        $page = $service->listPaginated(2, [
            'search' => 'a',
            'sortField' => 'name',
            'sortOrder' => -1,
        ]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $page);
        $this->assertSame(3, $page->total());
        $this->assertSame(['Gamma', 'Beta'], $page->pluck('name')->all());

        $filtered = $service->listPaginated(15, [
            'filters' => ['name' => 'Alpha'],
        ]);

        $this->assertSame(1, $filtered->total());
        $this->assertSame('Alpha', $filtered->first()->name);
    }

    public function test_find_with_string_primary_key(): void
    {
        Schema::create('string_widgets', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        $service = new class extends ModelService
        {
            protected string $model = StringWidget::class;
        };

        $widget = $service->create(['id' => 'w-1', 'name' => 'Alpha']);
        $this->assertInstanceOf(StringWidget::class, $widget);

        $found = $service->find('w-1');
        $this->assertInstanceOf(StringWidget::class, $found);
        $this->assertSame('Alpha', $found->name);
    }

    public function test_exception_thrown_when_model_not_defined(): void
    {
        $service = new class extends ModelService {};

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No model class configured');

        $service->list();
    }

    public function test_query_option_callback_allows_custom_constraints(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $service->create(['name' => 'Alpha']);
        $service->create(['name' => 'Beta']);

        $results = $service->list(['*'], [
            'query' => function (Builder $builder): void {
                $builder->where('name', 'Alpha');
            },
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('Alpha', $results->first()?->name);
    }

    public function test_query_option_used_with_pagination(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $service->create(['name' => 'Alpha']);
        $service->create(['name' => 'Beta']);

        $page = $service->listPaginated(15, [
            'query' => function (Builder $builder): void {
                $builder->where('name', 'Beta');
            },
        ]);

        $this->assertSame(1, $page->total());
        $this->assertSame('Beta', $page->first()?->name);
    }

    public function test_with_option_eager_loads_relations(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);
        WidgetNote::create(['widget_id' => $widget->id, 'content' => 'Note A']);
        WidgetNote::create(['widget_id' => $widget->id, 'content' => 'Note B']);

        $results = $service->list(['*'], [
            'with' => 'notes',
        ]);

        $this->assertTrue($results->first()?->relationLoaded('notes'));
        $this->assertCount(2, $results->first()?->notes);
    }

    public function test_with_count_option_eager_loads_counts_in_pagination(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);
        WidgetNote::create(['widget_id' => $widget->id, 'content' => 'Note A']);
        WidgetNote::create(['widget_id' => $widget->id, 'content' => 'Note B']);

        $page = $service->listPaginated(15, [
            'withCount' => 'notes',
        ]);

        $this->assertSame(1, $page->total());
        $this->assertSame(2, $page->first()?->notes_count);
    }

    public function test_update_by_key_updates_existing_model(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);

        $updated = $service->updateByKey($widget->id, ['name' => 'Beta']);

        $this->assertSame('Beta', $updated->name);
        $this->assertSame('Beta', $service->find($widget->id)?->name);
    }

    public function test_update_by_key_throws_when_model_missing(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = Widget::class;
        };

        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage(Widget::class);

        $service->updateByKey(999, ['name' => 'Ghost']);
    }

    public function test_delete_soft_deletes_when_force_not_requested(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = SoftWidget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);

        $result = $service->delete($widget);

        $this->assertTrue($result);
        $this->assertNull($service->find($widget->id));
        $this->assertTrue(SoftWidget::withTrashed()->find($widget->id)?->trashed());
    }

    public function test_delete_force_removes_soft_deleted_record(): void
    {
        $service = new class extends ModelService
        {
            protected string $model = SoftWidget::class;
        };

        $widget = $service->create(['name' => 'Alpha']);

        $result = $service->delete($widget, true);

        $this->assertTrue($result);
        $this->assertNull(SoftWidget::withTrashed()->find($widget->id));
    }
}

class Widget extends Model
{
    protected $guarded = [];

    protected $table = 'widgets';

    public function notes()
    {
        return $this->hasMany(WidgetNote::class);
    }
}

class StringWidget extends Model
{
    protected $guarded = [];

    protected $table = 'string_widgets';

    public $incrementing = false;

    protected $keyType = 'string';
}

class WidgetNote extends Model
{
    protected $guarded = [];

    protected $table = 'widget_notes';

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}

class SoftWidget extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'soft_widgets';
}
