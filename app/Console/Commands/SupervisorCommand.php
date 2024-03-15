<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

namespace App\Console\Commands;

use App\Interfaces\ShellCommandServiceInterface;
use Illuminate\Console\Command;

/**
 * Supervisor Command
 *
 * @package \App\Console\Commands
 */
class SupervisorCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'gk:supervisor {action}';

    /**
     * @var string
     */
    protected $description = 'Execute selected action for Supervisor programs
                                    {action : Selected Supervisor action}';

    /**
     * SupervisorCommand constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle() : void
    {
        $action = $this->argument('action');

        $availableActions = ['status', 'restart', 'start', 'stop'];

        try {
            if (in_array($action, $availableActions)) {
                $shellCommandService = app(ShellCommandServiceInterface::class);

                $command = sprintf('supervisorctl %s %s:', $action, config('supervisor.programs_group_name'));

                $result = $shellCommandService->executeCommand(explode(' ', $command));

                $this->info(__('artisan.gk_supervisor.success_message'));
                $this->info($result);
            } else {
                $this->error(__('artisan.gk_supervisor.invalid_action_argument', ['action' => $action]));
            }
        } catch (\Exception $exception) {
            $this->error(__('artisan.common.error'));
            $this->error(__('artisan.common.exception', ['exception' => $exception->getMessage()]));
        }
    }
}
