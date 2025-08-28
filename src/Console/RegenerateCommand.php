<?php

namespace AreiaLab\SlugUid\Console;

use Illuminate\Console\Command;
use AreiaLab\SlugUid\Facades\SlugUid;

class RegenerateCommand extends Command
{
    protected $signature = 'sluguid:regen {model}';
    protected $description = 'Re-generate slugs, UIDs, and sequences for a model';

    public function handle()
    {
        $modelClass = $this->argument('model');
        $this->info('Regenerating slugs/UIDs/sequences for ' . $modelClass);

        foreach ($modelClass::all() as $model) {
            SlugUid::assign($model);
            $model->save();
        }

        $this->info('Done.');
    }
}
