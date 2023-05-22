# vehiclecheck
Docker development implementation for Laravel 5.1.\* with:

- MySQL
- PHP7.1.0
- Aapache2
- Node-7

## Installation

- Clone this repository `git clone https://github.com/rahul-aecor/fleetmastr-2.30.0.git`
- Make sure you have docker installed on your local machine, you do not need to have php / mysql / node installed on your machine
- Copy `.env` file: `cp .env.example .env`
- Set the environment variables in `.env` file
- ## How to build and run container
```sh
# Build the image
`cd fleetmastr-2.30.0` && `docker-compose build`

# Run the container
`docker-compose up -d`

# See running containers
`docker-compose ps` 
```
- You can access the project at: `http://localhost:8000`
