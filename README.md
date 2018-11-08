# BSPostGallery

BSPostGallery is a simple plugin for WordPress. It replaces default
behavior of `gallery` shortcode by design inspired by google photos.
Implementation is based on react-grid-gallery (https://github.com/benhowell/react-grid-gallery)

Developed by: michal.nezerka@gmail.com

## Installation

Plugin is not registered in WordPress plugin repository so it is not possible
to find it in plugin search section. You can install it by downloading zip file
from github and manually uploading via WP admin interface: `Plugins -> Add New
-> Upload Plugin`

## Development

It easy to setup development environment based on docker images.  Attached
`docker-compose.yml` allows to start containers for both wp and mysql database
with plugin mounted inside wp container. Start containers by invoking:
```
docker-compose up -d
```
Web server hosting clean WP installation should listen on `http://localhost:8080`
Editing code of the plugin is immediately visible in WP. You can visit running
container:
```
docker-compose exec wordpress bash
```
or
```
docker-compose exec mysql bash
```
To stop all containers:
```
docker-compose down
```

## Known Issues

Gallery layout doesn't work well in standard WP templates (e.g. Twety Seventeen
theme). This can be fixed by introducing exception (override) of default CSS
styles in customization section. Adding following lines should fix layout problems:
```
/* This is fix of gallery layout implemented by BSPostGallery plugin */
.ReactGridGallery img {
        max-width: initial
}
```
