<?php

namespace Ouhaohan8023\IDInfo\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Ouhaohan8023\IDInfo\Facade\IDInfoFacade;

class IDInfoInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'id-info:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化省市区数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('============= start '.$this->description.' =================');
        IDInfoFacade::init();
        Log::info('============= end '.$this->description.' =================');
    }
}
