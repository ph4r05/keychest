<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;


class SetupEcho extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setupEcho
                                {--init-dev : Development configuration} 
                                {--init-prod : Production configuration}
                                {--force : Force overwrite}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravel Echo configuration file manager';

    /**
     * Root disk
     * @var
     */
    protected $disk;

    /**
     * Laravel Echo server file name
     */
    const ECHO_FILE = 'laravel-echo-server.json';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->disk = Storage::disk('root');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Initialization
        $this->handleInit();

        return 0;
    }

    /**
     * Ensure existence of the config file .env
     */
    protected function ensureExistence(){
        if ($this->disk->exists('.env')){
            return;
        }

        $this->disk->copy('.env.example', '.env');
    }

    /**
     * Handles environment setup
     */
    protected function handleInit(){
        $init_dev = $this->option('init-dev');
        $init_prod = $this->option('init-prod');

        if ($init_dev === false && $init_prod === false){
          return 0;
        }

        if ((intval($init_dev) + intval($init_prod)) != 1){
          throw new \Exception('Conflicting setting, only one of --init-dev, --init-prod can be active');
        }

        if ($this->disk->exists(self::ECHO_FILE) && $this->option('force') !== true){
          $this->warn('File ' . self::ECHO_FILE . ' already exists, use --force to overwrite');
          throw new \Exception('Cannot continue');
        }

        $sfile = $init_dev ? 'laravel-echo-server-dev.example.json' : 'laravel-echo-server-prod.example.json';
        $data = $this->disk->get($sfile);

        $js = $this->jsonLoad($data);
        $js->clients = [];
        $js->devMode = $init_dev === true;

        $new_data = json_encode($js, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        $this->disk->delete(self::ECHO_FILE);
        $this->disk->put(self::ECHO_FILE, $new_data);
        return 0;
    }

    /**
     * Processing DB data loaded in JSON, updating DB settings in the current .env
     * @param $js
     * @return int
     */
    protected function processDbData($js){
        $cfg = $js->config;

        $config_settings = [
            ['mysql_user', 'DB_USERNAME'],
            ['mysql_password', 'DB_PASSWORD'],
            ['mysql_db', 'DB_DATABASE']
        ];

        $new_config = [];
        foreach ($config_settings as $cfg_set){
            $cur = $this->absorbConfig($cfg, $cfg_set[0], $cfg_set[1]);
            if (!empty($cur)){
                $new_config[] = $cur;
            }
        }

        if (empty($new_config)){
            return 0;
        }

        DotenvEditor::setKeys($new_config);
        DotenvEditor::save();
        $this->info('DB config loaded from the scanner config');
        return 0;
    }

    /**
     * Helper to load json with comments
     * @param $data
     * @return mixed
     */
    protected function jsonLoad($data){
        $data = preg_replace('/^\s*\/\/.+$/m', '', $data);
        $data = preg_replace('/^\s*#.+$/m', '', $data);
        return json_decode($data);
    }

}
