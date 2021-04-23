<h1 align="center">
FuncAI PHP Backend
</h1>

<h4 align="center">
    Sample application which demonstrates the usage of <a href="https://github.com/funcai/funcai-php" target="_blank">FuncAI PHP</a>.<br>
    This backend powers the API for the <a href="https://php.funcai.net" target="_blank">FuncAI PHP docs</a>.
</h4>

### Overview
This is a Laravel application which provides API endpoint for different machine learning applications. The actual work is done via a queue, so that the server can handle many requests at once.

### Use the provided docker image
#### 1. Build the docker image 
Run the following command in the root of this project:

    docker build -t funcai-php-backend:latest -f docker/prod/Dockerfile .

#### 2. Start the docker container

    docker run -p 80:80 -it -n funcai funcai-php-backend:latest

#### 3. Download the necessary data
To install funcai-php we need the tensorflow library and the machine learning models. You can either download that data in the docker container:

    docker exec -it funcai bash
    php vendor/funcai/funcai-php/install.php

    # Install the image stylization model (optional)
    php vendor/funcai/funcai-php/install-stylization.php

    # Install the image classification model (optional)
    php vendor/funcai/funcai-php/install-imagenet21k.php

Alternatively you can mount the tensorflow and model folders into `/var/www/html/tensorflow` and `/var/www/html/models`. 
