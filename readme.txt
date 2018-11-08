# BSPostGallery

Contributors: Michal Nezerka <michal.nezerka@gmail.com>
License: MIT

BSPostGallery replaces default behavior of `gallery` shortcode by
design inspired by google photos. It is based on react-grid-gallery
(https://github.com/benhowell/react-grid-gallery)

## Development

It is quite easy to setup development environment based on docker images.
Attached `docker-compose.yml` allows to start containers for both wp and
mysql database with plugin mounted inside wp container. Start it by invoking:
```
docker-compose up -d
```
Web server hosting clean WP installation should listen on `http://localhost:8080`
Editing content of the plugin is directly visible in WP. You can visit running
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
