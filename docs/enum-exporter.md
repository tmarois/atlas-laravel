# Enum Exporter

Export Laravel PHP enums to your Vue application so both back and front end share the same enum definitions.

## Optional Configuration

Publish the config if you need to customize export paths or format:

```bash
php artisan vendor:publish --tag=atlas-config
```

## Export enums to Vue

Run the exporter to generate TypeScript or JavaScript enum files:

```bash
php artisan atlas:export-enums
```

## How it works

The command scans the configured enum paths and writes matching files to `resources/js/enums` (overridable via config). Each PHP enum is converted into a corresponding TypeScript/JavaScript enum and re-exported through an index file for easy imports.

If multiple enums share the same class name in different namespaces, the index file automatically aliases duplicates using their path segments to avoid export conflicts. Repeated segments that already appear in the class name are omitted to keep aliases concise:

```ts
// resources/js/enums/index.ts
export { ActionStatus } from './Action/ActionStatus';
export { ActionStatus as ActionWorkerStatus } from './Action/Worker/ActionStatus';
```

Define a PHP enum:

```php
namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
```

After running the exporter the following file is generated:

`resources/js/enums/UserStatus.ts`

```ts
export enum UserStatus {
    ACTIVE = 'active',
    INACTIVE = 'inactive',
}
```

You can then compare data to enum values in Vue components:

```vue
<script setup lang="ts">
import { UserStatus } from '@/enums';

const user = ref({ status: 'active' });

const isActive = computed(() => user.value.status === UserStatus.ACTIVE);
</script>
```

This keeps enum definitions synchronized across Laravel and Vue with full IDE support.
