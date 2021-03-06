FROM ubuntu:20.04

# Install all necessary Ubuntu packages
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y curl git zip unzip python3-pip libgmp-dev libmagic-dev libtinfo-dev libzmq3-dev libcairo2-dev libpango1.0-dev libblas-dev liblapack-dev gcc g++ wget php php-zmq php-zip && \
    rm -rf /var/lib/apt/lists/*

# Install Jupyter notebook
RUN pip3 install -U jupyter

# Install Composer
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

ENV LANG C.UTF-8
ENV LC_ALL C.UTF-8
ENV NB_USER jovyan
ENV NB_UID 1000
ENV HOME /home/${NB_USER}

RUN adduser --disabled-password \
    --gecos "Default user" \
    --uid ${NB_UID} \
    ${NB_USER}

USER ${NB_UID}

ENV PATH="/home/jovyan/.config/composer/vendor/bin:$PATH"
RUN composer global require rabrennie/jupyter-php-kernel
RUN jupyter-php-kernel --install

# Run the notebook
WORKDIR ${HOME}
RUN jupyter notebook --generate-config
CMD ["jupyter", "notebook", "--ip", "0.0.0.0"]
