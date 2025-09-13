<?php

namespace AreiaLab\SlugUid\Console;

use Illuminate\Console\Command;
use AreiaLab\SlugUid\Facades\SlugUid;

class RegenerateCommand extends Command
{
    protected $signature = 'sluguid:regen {model : The fully qualified model class, e.g. App\\Models\\Post}';
    protected $description = 'Re-generate slugs, UIDs, and sequences for a model';

    public function handle()
    {
        $modelClass = $this->argument('model');

        // Check if class exists
        if (!class_exists($modelClass)) {
            $this->error("Model class [$modelClass] does not exist.");
            return 1;
        }

        // Check if it's an Eloquent model
        if (!is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
            $this->error("[$modelClass] is not a valid Eloquent model.");
            return 1;
        }

        $this->info("Regenerating slugs/UIDs/sequences for: $modelClass");

        $count = 0;

        $modelClass::chunk(100, function ($models) use (&$count) {
            foreach ($models as $model) {
                SlugUid::assign($model, force: true); // <-- make sure it overwrites
                $model->save();
                $count++;
            }
        });

        $this->info("Done. Regenerated $count records.");
        return 0;
    }
}
