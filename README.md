Deflate.io Magento 1 Module
==========

With our official Magento 1 module you can easily enhance your Magento application with our easy to use API's. 

## Installation Instructions

### Manual Installation
You can download this repository as a zip archive [here](https://github.com/deflate/magento1/archive/master.zip) and upload the app & lib directories to your Magento application. Once uploaded you'll need to clear the cache.

### Composer Installation
You can install this module automatically via composer.

#### Single Command
Enter the below command into the root of your Magento project (where the composer.json file is located). This will add the VCS for our module and require it automatically.
```
composer config repositories.deflate vcs https://github.com/deflate/magento1.git && composer require deflate/magento1:"dev-master"
```

Alternatively you can add the following repository to your composer.json file manually:
```
{
  ...
  "repositories": [
    ...
    {
      "type": "vcs",
      "url": "https://github.com/deflate/magento1.git"
    }
    ...
  ]
  ...
}
```

Once you've added the repository you can require the `deflate/magento1` module.
```
composer require deflate/magento1:"dev-master"
```

Once that command is completed you'll need to clear your caches and the module will be enabled!

## Configuration
The module contains a number of configuration options that can be altered through `System > Configuration > Deflate - Image Compression`. The first step you'll need to take is linking your Deflate account, you can either pull your API key & secret manually from your account or use our account link functionality.

### Compress Settings
**Compression Type:** The type of compression you wish to complete on all images found by the module.

### Compression Areas
This section allows you to define which images Deflate should automatically compress

**Catalog Images**: Compress all images associated with products or categories.

**Compress Cached Resized Images**: Compress the images which have been resized by Magento for display on the front-end, we suggest leaving this enabled.

**CMS Images**: Compress all images uploaded through the CMS.

**Skin Images**: Compress all the images associated with all themes/skins.

**Skin Package/Themes**: Select which packages & themes you wish to compress, we understand you won't want to compress images in a theme you're not using.

## How to compress images
The system will automatically compress images for you in the background via a cron task. Alternatively you can access the interface for the module via `System > Image Compression`.
