# SlugUid Laravel Package

SlugUid is a Laravel package to automatically generate **slugs**, **unique identifiers (UIDs)**, and **sequence numbers** for your models. It supports configuration, facades, traits, and artisan commands.

---

## Requirements

- PHP >= 8.0
- Laravel >= 9.0
- Composer

---

## Installation

```bash
composer require areia-lab/slug-uid
```

Publish the configuration:

```bash
php artisan vendor:publish --provider="AreiaLab\SlugUid\SlugUidServiceProvider" --tag=sluguid-config
```

---

## Configuration

### Slug Settings

```php
'slug' => [
    'separator' => '-',
    'max_length' => 150,
    'source_columns' => ['title','name'],
    'regen_on_update' => true,
]
```

### UID Settings

```php
'uid' => [
    'prefix' => 'UID',
    'length' => 16,
    'driver' => 'uuid4',
]
```

### Sequence Settings

```php
'sequence' => [
    'prefix' => 'ORD',
    'padding' => 5,
    'column' => 'post_sequence',
    'scoped'  => true,
    'separator' => '-',
]
```

---

## Usage

Basic usage with the Facade:

```php
// Generate slug
SlugUid::slug('Hello World');

// Unique slug for model
SlugUid::uniqueSlug(Post::class, 'Hello World');

// Generate UID
SlugUid::uid();
SlugUid::uniqueUid(Post::class, 'USR');

// Sequence
SlugUid::sequence(Post::class, 'ORD', 4);
```

---

## Facade Methods

```php
SlugUid::slug('Hello World');
SlugUid::uniqueSlug($post);
SlugUid::uid('USR');
SlugUid::uniqueUid(Post::class, 'USR');
SlugUid::sequence(Post::class, 'INV');
```

---

## Traits

Instead of a single trait, you can use dedicated traits for slug, uid, and sequence:

```php
<?php

namespace App\Models;

use AreiaLab\SlugUid\Traits\HasSequence;
use AreiaLab\SlugUid\Traits\HasSlug;
use AreiaLab\SlugUid\Traits\HasUid;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSlug, HasUid, HasSequence;

    public $slug_column = 'slug';
    public $slug_source = 'title';

    public $uid_column = 'uid';
    public $uid_prefix = 'POST';

    public $sequence_column = 'post_sequence';
    public $sequence_prefix = 'PST';
    public $sequence_padding = 4;
    public $sequence_scoped = true;
    public $sequence_separator = '-';

    protected $fillable = ['title', 'slug', 'uid', 'post_sequence', 'description'];
}
```

---

## Artisan Commands

```bash
php artisan sluguid:regen App\Models\Post
```

---

## Features

- Customizable slug sources
- Automatic regeneration on update
- Hash-based UID generators
- Scoped sequence numbers per model type
- Artisan regen command
- Facade + Trait support
- Publishable configuration

---

## Examples

### Generate a basic slug

```php
return SlugUid::slug('Hello World');
// Output: hello-world
```

### Generate a unique slug for a model

```php
return SlugUid::uniqueSlug(Post::class, 'Hello World');
// Output: hello-world
// Output if slug exists: hello-world-1
```

### Generate a UID with prefix

```php
return SlugUid::uid('prefix');
// Output: prefix-65e1d5ff5201a7
```

### Generate a unique UID for a model

```php
return SlugUid::uniqueUid(Post::class, 'prefix');
// Output: prefix-xxxxxxxxxxxxxx (unique)
```

### Generate a sequence number for a model

```php
return SlugUid::sequence(Post::class, 'PST');
// Output: PST-0001

return SlugUid::sequence(Post::class, 'INV', 4);
// Output: INV-0001
```

### Generate slug, UID, and sequence from model creation

```php
return Post::create([
    'title' => 'hello world',
    'slug' => SlugUid::uniqueSlug(Post::class, 'hello world'),
    'uid' => SlugUid::uniqueUid(Post::class, 'post'),
    'post_sequence' => SlugUid::sequence(Post::class),
    'description' => 'This is a test post.'
]);
```

### Create a model using only model configuration

```php
return Post::create([
    'title' => 'hello world',
    'description' => 'This is a test post.'
]);
```

### Update model details

```php
$post = Post::first();
$post->update([
    'title' => 'My First Post Updated',
    'description' => 'This is a test post desc.'
]);
return $post;
```

---

## License

MIT License © [Your Name or Organization]
