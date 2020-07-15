<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeDbConstFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:ConstFile {fileName : Name of Model for which this file belongs to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Constant file for models';
    /**
     * @var Filesystem
     */
    private $files;

    /**
     * Create a new command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $db_const_path = $this->laravel->basePath().'/config/db_const';
        $fileName = $this->argument('fileName') . ".php";

        if(!$this->files->exists($db_const_path))
            $this->files->makeDirectory($db_const_path);

        if(!$this->files->exists($db_const_path . '/' . $fileName)) {
            $this->files->put($db_const_path . '/' . $fileName, "<?php \r\n\r\nreturn [];");
            $this->info($fileName . " successfully created.");
            }
        else
            $this->info($fileName . " already exists");

    }
}
