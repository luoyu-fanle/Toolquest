# Toolquest
TDD

## Code Review

This code review was written from 3 different points of view; Code Security Audit and, Code Design Audit, and User Tests

### Disclaimer

The reviewer recognizes that the repository implements intentional vulnerabilities and publishes their Security Audit without including the deliberate flaws and bad practices.

The reviewer makes no claims of full coverage.

### Code Security Audit

#### Finding 1: Strict Types Not Enforced

The project is mainly written in PHP. By default, PHP exhibits type juggling behavior [1] which can cause unexpected bugs and vulnerabilities [2].

It is recommended to enforce strict type checking [3]

[1] https://www.php.net/manual/en/language.types.type-juggling.php
[2] https://github.com/swisskyrepo/PayloadsAllTheThings/blob/master/Type%20Juggling/README.md
[3] https://stackoverflow.com/questions/48723637/what-do-strict-types-do-in-php


#### Finding 2: Harcoded Database Credentials in docker-compose.yml

Default database credentials were identified in `/test-toolquest/docker-compose.yml`. It's recommended to pass these credentials to docker instances via other means [4]

[4] https://stackoverflow.com/questions/22651647/docker-and-securing-passwords

#### Finding 3: MySQL Database Server Served on 0.0.0.0

When `/test-toolquest/Dockerfile` is deployed successfully, it exposes the MySQL server on all interfaces as can be seen below

```bash
tcp        0      0 0.0.0.0:3306            0.0.0.0:*               LISTEN      -                   
```

It is encouraged to serve MySQL on localhost only by changing `3306:3306` to `127.0.0.1:3306:3306` on `test-toolquest/docker-compose.yml` [5]

[5] https://www.reddit.com/r/HomeServer/comments/1b2c8m7/mysql_db_hacked_within_docker_container_on_my_vps/

#### Finding 4 (Intentional): decodeRandomJWTToken() Does Not Verify JWT Signature

Intentional flaw, need to use Firebase's `JWT::decode()`, not PHP's `base64_decode()` nice one!

#### Finding 5 (Intentional): verifyJwtTokenWeakKey() Uses Supposed Weak Key

The JWT from the config is not a randomly generated key `my_long_long_long_long_long_long_long_long_secret_key_1234567890`

BUT, depending on the definition of a strong key, this key can be considered strong because of the sheer length it has.

The reviewer argues that the vulnerability here should rather be named "hardcoded JWT secret key".

#### Finding 6 (Intentional): verifyJwtTokenWeakKey() Uses Supposed Weak Key

### User Tests

#### Finding 1: Error in Docker Deployment

The reviewer encountered errors in the docker-compose workflow under `/test-toolquest/`. The current setup likely has problems regarding execution of Laravel (artisan).

It's encouraged to test the docker deployment and to document how end-users can deploy the containers.

### Code Design Audit

#### Finding 1: Database Access From Outside Models directory

- Is `/test-toolquest/Service/SQLService.php` supposed to take database actions directly without a Model?

- `/test-toolquest/Service/VulnerableController.php:login` takes direct database actions but that is likely intentional to expose a vulnerability

- `/test-toolquest/Controller/ProfileController.php:show` performs direct database access, the function looks intentionally vulnerable but is not used by routers. The reviewer doesn't know what's up with that

