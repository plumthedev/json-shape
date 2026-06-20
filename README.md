# Json Shape

> TBA

## Development

This project is developed entirely inside Docker, so the only tools you need on
your machine are:

- [Docker](https://docs.docker.com/get-docker/) (with the Compose plugin)
- [Make](https://www.gnu.org/software/make/)

Everything else (PHP, Composer) runs inside the containers defined in
[`docker-compose.yml`](docker-compose.yml).

### Make commands

| Command                      | Description                                                        |
| ---------------------------- | ------------------------------------------------------------------ |
| `make composer <args>`       | Run Composer inside the container, e.g. `make composer install`.   |
| `make php <args>`            | Run PHP inside the container, e.g. `make php -v`.                   |

Both commands forward all arguments straight to the underlying binary, so any
Composer or PHP invocation works:

```bash
make composer install
make composer update
make composer audit
make php -v
```

### Switching PHP version

The default PHP version is `8.4`. Override it per command with the
`PHP_VERSION` variable (`82`, `83` or `84`):

```bash
make composer install PHP_VERSION=82
make php -v PHP_VERSION=83
```

## License

Released under the [MIT License](LICENSE).