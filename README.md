# HOMM pixx.io plugin for Craft CMS

Craft CMS pixx.io adapter

> This plugin requests all images from pixx.io

![Screenshot](resources/img/plugin-logo.svg)

## Requirements

This plugin requires Craft CMS 4.x.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require homm/hommpixxio

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for HOMM pixx.io.

## HOMM pixx.io Overview

This plugin requests all images from pixx.io

## Configuring HOMM pixx.io

Go to _Settings > HOMM pixx.io_:

Here you can set an API key.

## Using HOMM pixx.io

1. Go to _Settings > Fields_ and create a new field.
2. Within the _Field Type_ choose _HOMM pixx.io_
3. Assign the field to a section
4. Now you can choose a file from pixx.io within your entries

Basic usage in the template (Twig):

```html
<img src="{{ entry.pixxioField.originalFileURL }}" alt="">
```

## HOMM pixx.io Roadmap

Some things to do, and ideas for potential features:

* tbd...

Brought to you by [HOMM interactive](https://github.com/HOMMinteractive)
