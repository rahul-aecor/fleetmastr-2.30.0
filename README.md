# Fleetmastr-2.30.0 Vehiclecheck:
## Prerequisites
Docker development implementation for Laravel 5.1.\* with:

* MySQL 5.7.38
* PHP 7.1.0
* NVM 7.8.0
* NPM 4.2.0

## Installation requirements
* *Please refer to the* [Documentation](https://docs.docker.com/desktop/install/linux-install/)
1. Install docker
  ```sh 
  apt install docker.io
  ```
2. Install docker-compose
  ```sh 
  curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker- 
  compose
  ```
3. Assign permission to docker-compose
  ```sh 
  chmod +x /usr/local/bin/docker-compose
  ```
4. Check docker-compose version
  ```sh
  docker-compose --version
  ```

* *Make sure you have docker installed on your local machine, you do not need to have php / mysql / node installed on your machine.*

5. Check node version
  ```sh 
  node -v
  ```
6. Check npm version
  ```sh
  npm -v
  ```
7. Install git
  ```sh 
  apt install git
  ```
8. Clone the repository 
  ```sh
  git clone https://github.com/rahul-aecor/fleetmastr-2.30.0.git
  ```
9. Go to fleetmastr-2.30.0 directory
  ```sh
  cd fleetmastr-2.30.0 
  ```
10. Initializing git 
  ```sh 
  git init
  ```
11. Configure user name and email
  ```sh 
  git config --global user.name 'abc'   
  git config --global user.email 'abc@gmail.com'
  ```
- Copy `.env` file: `cp .env.example .env`
## How to build and run container
* *Before build image make sure are you present in git repo where is Dockerfile and docker-compose.yml available.* 

1. Build the image
  ```sh
  cd fleetmastr-2.30.0 && docker-compose build
  ```
2. Run the container
  ```sh
  docker-compose up -d
  ```
3. See created images
  ```sh
  docker images
  ```
4. See running containers
  ```sh
  docker-compose ps 
  ```
* You can access the project at: `http://localhost:8000`
