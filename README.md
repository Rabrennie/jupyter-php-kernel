# jupyter-php-kernel

This is a PHP kernel for Jupyter (https://jupyter.org/).

## Requirements

* PHP 7.4+
* jupyter
* php-zmq extension

## Installation

### Composer

Ensure you have composer installed with the bin folder in your PATH

```bash
composer global require rabrennie/jupyter-php-kernel
```

once that has installed run the following command

```bash
jupyter-php-kernel --install
```

Run jupyter notebook and create a new notebook with kernel type PHP.

### Docker

Check out the [Dockerfile](Dockerfile)
