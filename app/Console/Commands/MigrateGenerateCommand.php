<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Way\Generators\Generator;
use Way\Generators\Filesystem\Filesystem;
use Way\Generators\Compilers\TemplateCompiler;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;

use Xethron\MigrationsGenerator\Syntax\AddToTable;
use Xethron\MigrationsGenerator\Syntax\DroppedTable;
use Xethron\MigrationsGenerator\Syntax\AddForeignKeysToTable;
use Xethron\MigrationsGenerator\Syntax\RemoveForeignKeysFromTable;
use Xethron\MigrationsGenerator\MigrateGenerateCommand as BaseMigrateGenerateCommand;

use Illuminate\Contracts\Config\Repository as Config;

class MigrateGenerateCommand extends BaseMigrateGenerateCommand {

	/**
	 * The console command name.
	 * @var string
	 */
	protected $name = 'migrate:generateForTests';

	/**
	 * The console command description.
	 * @var string
	 */
	protected $description = 'Generate a migration from an existing table structure for tests - '
                            .'separate namespace from normal migrations.';

    /**
     * @param \Way\Generators\Generator $generator
     * @param \Way\Generators\Filesystem\Filesystem $file
     * @param \Way\Generators\Compilers\TemplateCompiler $compiler
     * @param \Illuminate\Database\Migrations\MigrationRepositoryInterface $repository
     * @param \Illuminate\Config\Repository|Config $config
     */
	public function __construct(
		Generator $generator,
		Filesystem $file,
		TemplateCompiler $compiler,
		MigrationRepositoryInterface $repository,
		Config $config
	)
	{
		parent::__construct( $generator, $file, $compiler, $repository, $config );
	}

	/**
	 * Generate tables and index migrations.
	 *
	 * @param  array $tables List of tables to create migrations for
	 * @return void
	 */
	protected function generateTablesAndIndices( array $tables )
	{
		$this->method = 'create';

		foreach ( $tables as $table ) {
			$this->table = $table;
			$this->migrationName = 'create_'. $this->table .'_table_mtgen';
			$this->fields = $this->schemaGenerator->getFields( $this->table );

			$this->generate();
		}
	}

	/**
	 * Generate foreign key migrations.
	 *
	 * @param  array $tables List of tables to create migrations for
	 * @return void
	 */
	protected function generateForeignKeys( array $tables )
	{
		$this->method = 'table';

		foreach ( $tables as $table ) {
			$this->table = $table;
			$this->migrationName = 'add_foreign_keys_to_'. $this->table .'_table';
			$this->fields = $this->schemaGenerator->getForeignKeyConstraints( $this->table );

			$this->generate();
		}
	}

	/**
	 * The path where the file will be created
	 *
	 * @return string
	 */
	protected function getFileGenerationPath()
	{
		$path = $this->getPathByOptionOrConfig( 'path', 'migration_target_path' );
		$migrationName = str_replace('/', '_', $this->migrationName);
		$fileName = $this->getDatePrefix() . '_' . $migrationName . '.php';

		return "{$path}/{$fileName}";
	}

    /**
     * Get the date prefix for the migration.
     * Returns constant prefix to avoid duplication.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        //return parent::getDatePrefix();
        return '2017_01_01_000000';
    }


    /**
	 * Fetch the template data
	 *
	 * @return array
	 */
	protected function getTemplateData()
	{
		if ( $this->method == 'create' ) {
			$up = (new AddToTable($this->file, $this->compiler))->run($this->fields, $this->table, $this->connection, 'create');
			$down = (new DroppedTable)->drop($this->table, $this->connection);
		}

		if ( $this->method == 'table' ) {
			$up = (new AddForeignKeysToTable($this->file, $this->compiler))->run($this->fields, $this->table, $this->connection);
			$down = (new RemoveForeignKeysFromTable($this->file, $this->compiler))->run($this->fields, $this->table, $this->connection);
		}

		return [
			'CLASS' => ucwords(camel_case($this->migrationName)),
			'UP'    => $up,
			'DOWN'  => $down
		];
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['tables', InputArgument::OPTIONAL, 'A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['connection', 'c', InputOption::VALUE_OPTIONAL, 'The database connection to use.', $this->config->get( 'database.default' )],
			['tables', 't', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to Generate Migrations for separated by a comma: users,posts,comments'],
			['ignore', 'i', InputOption::VALUE_OPTIONAL, 'A list of Tables you wish to ignore, separated by a comma: users,posts,comments' ],
			['path', 'p', InputOption::VALUE_OPTIONAL, 'Where should the file be created?'],
			['templatePath', 'tp', InputOption::VALUE_OPTIONAL, 'The location of the template for this generator'],
			['defaultIndexNames', null, InputOption::VALUE_NONE, 'Don\'t use db index names for migrations'],
			['defaultFKNames', null, InputOption::VALUE_NONE, 'Don\'t use db foreign key names for migrations'],
		];
	}

}
