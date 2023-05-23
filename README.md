# Vehiclecheck:
# Prerequisite

Docker development implementation for Laravel 5.1.\* with:

- MySQL5.7.38
- PHP7.1.0
- Node-7

## Installation


- Install docker `apt install docker.io`
- Install docker-compose `curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose` next `chmod +x /usr/local/bin/docker-compose` next check docker-compose version `docker-compose --version`
- Make sure you have docker installed on your local machine, you do not need to have php / mysql / node installed on your machine
- Install docker `apt install git`
- Clone this repository `git clone https://github.com/rahul-aecor/fleetmastr-2.30.0.git`
- Go to fleetmastr-2.30.0 directory `cd fleetmastr-2.30.0 `
- Initializing git `git init`
- Configure user name and email `git config --global user.name 'abc'` and  `git config --global user.email 'abc@gmail.com'`
- Copy `.env` file: `cp .env.example .env`
- Set the environment variables in `.env` file
## How to build and run container
```sh
# Build the image
cd fleetmastr-2.30.0 && docker-compose build

# Run the container
docker-compose up -d

# See running containers
docker-compose ps 
```
- You can access the project at: `http://localhost:8000`
