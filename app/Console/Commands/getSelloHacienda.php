<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\obtenerSelloHacienda;
use Illuminate\Support\Facades\Log;

class getSelloHacienda extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:stamp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Obtener Sello para DTE';

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
        Log::info("Inicio de envío de DTE para obtener sello desde Command");
        dispatch(new obtenerSelloHacienda());
        Log::info("Fin de envío de DTE para obtener sello desde Command");
    }
}
