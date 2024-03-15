<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Console\Commands;

use App\Helpers\MysqlDatabaseHelper;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

/**
 * Database Import Command
 *
 * @package \App\Console\Commands
 */
class DatabaseImportCommand extends Command
{
    use ConfirmableTrait;

    /**
     * @var string
     */
    protected $signature = 'db:import {--P|path=} {--force}';

    /**
     * @var string
     */
    protected $description = 'Run DB tables import
                                    {--P|path= : Path to import file}
                                    {--force   : Force the import even in production mode}';

    /**
     * DatabaseImportCommand constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function handle() : mixed
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $path = $this->option('path');

        if ($path) {
            try {
                $this->info(__('artisan.database_import.start_message'));

                MysqlDatabaseHelper::import(config('database.default'), $path);

                $this->info(__('artisan.database_import.success_message'));
            } catch (\Exception $exception) {
                $this->error(__('artisan.common.error'));
                $this->error(__('artisan.common.exception', ['exception' => $exception->getMessage()]));
            }
        } else {
            $this->error(__('artisan.database_import.missing_path_argument'));
        }
    }
}
