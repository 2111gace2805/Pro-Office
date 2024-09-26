<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\enviarContingencia;
use Illuminate\Support\Facades\Log;

class envioContingencia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:contingencia';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envio de DTE en contingencia';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info("Inicio de envío de DTE en contingencia desde Command");
        dispatch(new enviarContingencia());
        Log::info("Fin de envío de DTE en contingencia desde Command");
    }
}
