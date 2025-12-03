<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class GenerateDataTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:datatable
                            {model : The model name to generate DataTable for}
                            {--with-actions : Generate with action column and view file}
                            {--with-relations : Include relationship columns}
                            {--with-index : Generate index view file}
                            {--force : Overwrite existing file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a comprehensive DataTable class based on a model with intelligent column detection and filtering';

    /**
     * The model instance
     */
    protected ?Model $modelInstance = null;

    /**
     * The model class name
     */
    protected string $modelClass;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('model');
        $this->modelClass = "App\\Models\\" . Str::studly($modelName);
        $className = Str::studly($modelName) . 'DataTable';
        $filePath = app_path("DataTables/Custom/{$className}.php");

        // Validate model exists
        if (!class_exists($this->modelClass)) {
            $this->error("Model '{$this->modelClass}' does not exist!");
            return 1;
        }

        // Ensure the directory exists
        if (!File::exists(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Check if the file already exists
        if (File::exists($filePath) && !$this->option('force')) {
            if (!$this->confirm("The file {$className}.php already exists. Do you want to overwrite it?")) {
                $this->info("Operation cancelled.");
                return 0;
            }
        }

        try {
            // Initialize model instance
            $this->modelInstance = new $this->modelClass();

            // Fetch table columns
            $columns = $this->getModelTableColumns();
            if (!$columns) {
                $this->error("Unable to fetch columns for the model '{$modelName}'. Ensure the model and table exist.");
                return 1;
            }

            // Get relationships if requested
            $relationships = $this->option('with-relations') ? $this->getModelRelationships() : [];

            // Generate the content
            $content = $this->getStubContent($className, $columns, $modelName, $relationships);

            // Write the content to the file
            File::put($filePath, $content);

            $this->info("DataTable class {$className} created successfully at {$filePath}");

            // Generate action view file if requested
            if ($this->option('with-actions')) {
                $this->generateActionViewFile($modelName);
            }

            // Generate index view file if requested
            if ($this->option('with-index')) {
                $this->generateIndexViewFile($modelName);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Error generating DataTable: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Fetch the columns of the model's table with type information.
     *
     * @return array|null
     */
    private function getModelTableColumns(): ?array
    {
        $table = $this->modelInstance->getTable();

        if (!Schema::hasTable($table)) {
            return null;
        }

        $columns = Schema::getColumnListing($table);
        $columnDetails = [];

        foreach ($columns as $column) {
            $columnDetails[$column] = [
                'name' => $column,
                'type' => Schema::getColumnType($table, $column),
                'nullable' => $this->isColumnNullable($table, $column),
            ];
        }

        return $columnDetails;
    }

    /**
     * Check if a column is nullable
     */
    private function isColumnNullable(string $table, string $column): bool
    {
        try {
            $columnInfo = Schema::getConnection()->getDoctrineColumn($table, $column);
            return !$columnInfo->getNotnull();
        } catch (\Exception $e) {
            // Fallback: assume nullable for most columns except id and timestamps
            return !in_array($column, ['id', 'created_at', 'updated_at']);
        }
    }

    /**
     * Get model relationships
     */
    private function getModelRelationships(): array
    {
        $relationships = [];
        $reflection = new ReflectionClass($this->modelInstance);

        foreach ($reflection->getMethods() as $method) {
            if ($method->isPublic() &&
                $method->getNumberOfParameters() === 0 &&
                str_starts_with($method->getName(), 'get') &&
                str_ends_with($method->getName(), 'Attribute') === false) {

                $methodName = $method->getName();
                $relationName = Str::snake(str_replace('get', '', $methodName));

                try {
                    $returnType = $method->getReturnType();
                    if ($returnType && str_contains($returnType->getName(), 'Relation')) {
                        $relationships[] = $relationName;
                    }
                } catch (\Exception $e) {
                    // Skip methods that can't be analyzed
                }
            }
        }

        return $relationships;
    }

    /**
     * Get the content of the stub.
     */
    private function getStubContent(string $className, array $columns, string $modelName, array $relationships = []): string
    {
        $modelClass = Str::studly($modelName);
        $tableName = Str::snake($modelName);

        // Generate column definitions
        $columnDefinitions = $this->generateColumnDefinitions($columns, $relationships);

        // Generate filter definitions
        $filterDefinitions = $this->generateFilterDefinitions($columns);

        // Generate searchable relations
        $searchableRelations = $this->generateSearchableRelations($relationships);

        // Generate edit columns
        $editColumns = $this->generateEditColumns($columns);

        // Generate with relationships
        $withRelationships = $this->generateWithRelationships($relationships);

        // Generate action column if requested
        $actionColumn = $this->option('with-actions') ?
            "            Column::create('action')->setTitle('Actions')->setSearchable(false)->setOrderable(false)," : '';

        // Generate action column method
        $actionColumnMethod = $this->generateActionColumn($modelName);

        // Generate filter logic
        $filterLogic = $this->generateFilterLogic($columns);

        // Generate raw columns
        $rawColumns = $this->generateRawColumns($columns);

        // Load template
        $template = $this->loadTemplate('datatable.stub');

        // Replace placeholders
        return str_replace([
            '{className}',
            '{modelClass}',
            '{columnDefinitions}',
            '{actionColumn}',
            '{filterDefinitions}',
            '{searchableRelations}',
            '{withRelationships}',
            '{editColumns}',
            '{actionColumnMethod}',
            '{filterLogic}',
            '{rawColumns}'
        ], [
            $className,
            $modelClass,
            $columnDefinitions,
            $actionColumn,
            $filterDefinitions,
            $searchableRelations,
            $withRelationships,
            $editColumns,
            $actionColumnMethod,
            $filterLogic,
            $rawColumns
        ], $template);
    }

    /**
     * Generate column definitions with intelligent defaults
     */
    private function generateColumnDefinitions(array $columns, array $relationships): string
    {
        $definitions = [];
        $excludeColumns = ['id', 'password', 'remember_token', 'email_verified_at', 'phone_verified_at'];

        foreach ($columns as $columnName => $columnInfo) {
            if (in_array($columnName, $excludeColumns)) {
                continue;
            }

            $definition = "            Column::create('{$columnName}')";

            // Set custom titles for common columns
            $title = $this->getColumnTitle($columnName);
            if ($title !== $columnName) {
                $definition .= "->setTitle('{$title}')";
            }

            // Set non-orderable for certain columns
            if (in_array($columnName, ['created_at', 'updated_at'])) {
                $definition .= "->setOrderable(false)";
            }

            $definitions[] = $definition . ',';
        }

        return implode("\n", $definitions);
    }

    /**
     * Generate filter definitions based on column types
     */
    private function generateFilterDefinitions(array $columns): string
    {
        $filters = [];

        foreach ($columns as $columnName => $columnInfo) {
            $filter = $this->generateFilterForColumn($columnName, $columnInfo);
            if ($filter) {
                $filters[] = $filter;
            }
        }

        return implode("\n", $filters);
    }

    /**
     * Generate filter for a specific column
     */
    private function generateFilterForColumn(string $columnName, array $columnInfo): ?string
    {
        $type = $columnInfo['type'];

        // Date columns
        if (in_array($type, ['date', 'datetime', 'timestamp'])) {
            return "            '{$columnName}' => Filter::date('" . $this->getColumnTitle($columnName) . "', 'today'),";
        }

        // Boolean columns
        if (in_array($type, ['boolean', 'tinyint']) && in_array($columnName, ['is_active', 'is_registered', 'show', 'visible'])) {
            $label = $this->getColumnTitle($columnName);
            return "            '{$columnName}' => Filter::select('{$label}', [
                '1' => 'Yes',
                '0' => 'No'
            ]),";
        }

        // Enum-like columns
        if (in_array($columnName, ['status', 'type', 'active_profile_type'])) {
            $label = $this->getColumnTitle($columnName);
            return "            '{$columnName}' => Filter::select('{$label}', [
                // Add your enum values here
            ]),";
        }

        return null;
    }

    /**
     * Generate searchable relations
     */
    private function generateSearchableRelations(array $relationships): string
    {
        if (empty($relationships)) {
            return "            //";
        }

        $relations = [];
        foreach ($relationships as $relation) {
            $relations[] = "            '{$relation}' => ['name'], // Add searchable columns";
        }

        return implode("\n", $relations);
    }

    /**
     * Generate edit columns for formatting
     */
    private function generateEditColumns(array $columns): string
    {
        $editColumns = [];

        foreach ($columns as $columnName => $columnInfo) {
            $editColumn = $this->generateEditColumnForField($columnName, $columnInfo);
            if ($editColumn) {
                $editColumns[] = $editColumn;
            }
        }

        return implode("\n", $editColumns);
    }

    /**
     * Generate edit column for specific field
     */
    private function generateEditColumnForField(string $columnName, array $columnInfo): ?string
    {
        $type = $columnInfo['type'];

        // Boolean columns
        if (in_array($type, ['boolean', 'tinyint']) && in_array($columnName, ['is_active', 'is_registered', 'show'])) {
            return "            ->editColumn('{$columnName}', function (\$model) {
                return \$model->{$columnName}
                    ? '<span class=\"badge badge-success\">Yes</span>'
                    : '<span class=\"badge badge-danger\">No</span>';
            })";
        }

        // Date columns
        if (in_array($type, ['date', 'datetime', 'timestamp'])) {
            return "            ->editColumn('{$columnName}', function (\$model) {
                return \$model->{$columnName}?->format('Y-m-d H:i:s') ?? 'N/A';
            })";
        }

        // Status columns
        if ($columnName === 'status') {
            return "            ->editColumn('{$columnName}', function (\$model) {
                return match (\$model->{$columnName}) {
                    'active' => '<span class=\"badge badge-success\">Active</span>',
                    'inactive' => '<span class=\"badge badge-danger\">Inactive</span>',
                    'pending' => '<span class=\"badge badge-warning\">Pending</span>',
                    default => '<span class=\"badge badge-light\">' . ucfirst(\$model->{$columnName}) . '</span>',
                };
            })";
        }

        return null;
    }

    /**
     * Generate with relationships for eager loading
     */
    private function generateWithRelationships(array $relationships): string
    {
        if (empty($relationships)) {
            return '';
        }

        $relations = implode("', '", $relationships);
        return "->with(['{$relations}'])";
    }

    /**
     * Generate filter logic
     */
    private function generateFilterLogic(array $columns): string
    {
        $filterLogic = [];

        foreach ($columns as $columnName => $columnInfo) {
            $type = $columnInfo['type'];

            if (in_array($type, ['boolean', 'tinyint']) || in_array($columnName, ['status', 'type', 'active_profile_type'])) {
                $filterLogic[] = "                if (!empty(\$filters['{$columnName}'])) {
                    \$query->where('{$columnName}', \$filters['{$columnName}']);
                }";
            } elseif (in_array($type, ['date', 'datetime', 'timestamp'])) {
                $filterLogic[] = "                if (!empty(\$filters['{$columnName}'])) {
                    \$query->whereDate('{$columnName}', \$filters['{$columnName}']);
                }";
            }
        }

        return implode("\n", $filterLogic);
    }

    /**
     * Generate raw columns array
     */
    private function generateRawColumns(array $columns): string
    {
        $rawColumns = [];

        foreach ($columns as $columnName => $columnInfo) {
            $type = $columnInfo['type'];

            if (in_array($type, ['boolean', 'tinyint']) ||
                in_array($columnName, ['status', 'type', 'active_profile_type']) ||
                in_array($type, ['date', 'datetime', 'timestamp'])) {
                $rawColumns[] = "'{$columnName}'";
            }
        }

        if ($this->option('with-actions')) {
            $rawColumns[] = "'action'";
        }

        return implode(', ', $rawColumns);
    }

    /**
     * Get human-readable column title
     */
    private function getColumnTitle(string $columnName): string
    {
        $titles = [
            'full_name' => 'Full Name',
            'national_id' => 'National ID',
            'birth_date' => 'Birth Date',
            'is_active' => 'Status',
            'is_registered' => 'Registered',
            'active_profile_type' => 'Profile Type',
            'created_at' => 'Created',
            'updated_at' => 'Updated',
        ];

        return $titles[$columnName] ?? str_replace('_', ' ', ucfirst($columnName));
    }

    /**
     * Generate action column for DataTable
     */
    private function generateActionColumn(string $modelName): string
    {
        if (!$this->option('with-actions')) {
            return '';
        }

        $routePrefix = Str::kebab($modelName);

        return "            ->addColumn('action', function (\$model) {
                return view('pages.{$routePrefix}.columns._actions', compact('model'))->render();
            })";
    }

    /**
     * Generate action view file
     */
    private function generateActionViewFile(string $modelName): void
    {
        $viewPath = resource_path("views/pages/{$modelName}/columns/_actions.blade.php");
        $viewDir = dirname($viewPath);

        if (!File::exists($viewDir)) {
            File::makeDirectory($viewDir, 0755, true);
        }

        $viewContent = $this->generateActionViewContent($modelName);
        File::put($viewPath, $viewContent);

        $this->info("Action view file created at {$viewPath}");
    }

    /**
     * Generate action view content
     */
    private function generateActionViewContent(string $modelName): string
    {
        $routePrefix = Str::kebab($modelName);

        // Load template
        $template = $this->loadTemplate('action-view.stub');

        // Replace placeholders
        return str_replace('{routePrefix}', $routePrefix, $template);
    }

    /**
     * Generate index view file
     */
    private function generateIndexViewFile(string $modelName): void
    {
        $viewPath = resource_path("views/pages/{$modelName}/index.blade.php");
        $viewDir = dirname($viewPath);

        if (!File::exists($viewDir)) {
            File::makeDirectory($viewDir, 0755, true);
        }

        // Check if the file already exists
        if (File::exists($viewPath) && !$this->option('force')) {
            if (!$this->confirm("The index view file already exists. Do you want to overwrite it?")) {
                $this->info("Index view file generation skipped.");
                return;
            }
        }

        $viewContent = $this->generateIndexViewContent($modelName);
        File::put($viewPath, $viewContent);

        $this->info("Index view file created at {$viewPath}");
    }

    /**
     * Generate index view content
     */
    private function generateIndexViewContent(string $modelName): string
    {
        $routePrefix = Str::kebab($modelName);
        $tableId = Str::snake($modelName) . '_table';
        $actions = $this->option('with-actions') ? 'true' : 'false';

        // Load template
        $template = $this->loadTemplate('index-view.stub');

        // Replace placeholders
        return str_replace([
            '{tableId}',
            '{routePrefix}',
            '{actions}'
        ], [
            $tableId,
            $routePrefix,
            $actions
        ], $template);
    }

    /**
     * Load template from file
     */
    private function loadTemplate(string $templateName): string
    {
        $templatePath = __DIR__ . '/templates/' . $templateName;

        if (!File::exists($templatePath)) {
            throw new \Exception("Template file not found: {$templatePath}");
        }

        return File::get($templatePath);
    }
}
