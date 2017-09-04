<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;


class Setup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup
                                {--dev : Development configuration} 
                                {--test : Test configuration} 
                                {--prod : Production configuration} 
                                {--db-config= : DB config file to use for configuration} 
                                {--db-config-auto : DB config file to use for configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'App configuration file manager';

    /**
     * Root disk
     * @var
     */
    protected $disk;

    /**
     * @var DotenvEditor
     */
    protected $editor;

    /**
     * Paths for auto-detection
     */
    const DB_CONFIG_PATHS = [
        '/etc/enigma-keychest-scanner/config.json',
        '/opt/enigma-keychest-scanner/config.json',
    ];

    /**
     * Create a new command instance.
     * @param DotenvEditor $editor
     */
    public function __construct(DotenvEditor $editor)
    {
        parent::__construct();
        $this->disk = Storage::disk('root');
        $this->editor = $editor;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // make sure .env exists
        $this->ensureExistence();

        // env setting
        $this->handleEnv();

        // load DB settings from scanner
        $this->handleDbConfig();

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
    protected function handleEnv(){
          $env_prod = $this->option('prod');
          $env_test = $this->option('test');
          $env_dev = $this->option('dev');

          if ($env_prod === false && $env_test === false && $env_dev === false){
              return 0;
          }

          if ((intval($env_prod) + intval($env_test) + intval($env_dev)) != 1){
              throw new \Exception('Conflicting setting, only one of --prod, --test, --dev can be active');
          }

          DotenvEditor::setKey('APP_DEBUG', !$env_prod);
          if ($env_prod){
              DotenvEditor::setKey('APP_ENV', 'prod');
          } elseif ($env_test){
              DotenvEditor::setKey('APP_ENV', 'test');
          } else {
              DotenvEditor::setKey('APP_ENV', 'dev');
          }

          DotenvEditor::save();
          return 0;
    }

    /**
     * Handles DB config loading from the scanner
     * @return int
     * @throws \Exception
     */
    protected function handleDbConfig(){
        $db_conf = $this->option('db-config');
        $db_conf_auto = $this->option('db-config-auto');
        if (empty($db_conf) && $db_conf_auto === false){
            return 0;
        }

        $db_conf_data = null;
        if (!empty($db_conf)){
            $db_conf_data = file_get_contents($db_conf);
        }

        if (empty($db_conf) && $db_conf_auto){
            $db_conf_data = $this->autodetectConfig();
        }

        if (empty($db_conf_data)){
            $this->error('Could not load DB config');
            throw new \Exception('Cannot continue');
        }

        $conf_data = $this->jsonLoad($db_conf_data);

        if (!isset($conf_data->config)){
            $this->warn('Config file loaded but does not contains config data');
            return 1;
        }

        return $this->processDbData($conf_data);
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
     * Attempts to autodetect and load scanner config file.
     * @return bool|null|string
     */
    protected function autodetectConfig(){
        foreach (self::DB_CONFIG_PATHS as $curPath) {
            try{
                if (file_exists($curPath)){
                    return file_get_contents($curPath);
                }
            } catch (\Exception $e){

            }
        }

        return null;
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

    /**
     * If the key is set its absorbed to the .env
     * @param $js
     * @param $key
     * @param $config_key
     * @return array
     */
    protected function absorbConfig($js, $key, $config_key) {
        if (!isset($js->$key)){
            return [];
        }

        return ['key' => $config_key, 'value' => $js->$key];
    }

}
