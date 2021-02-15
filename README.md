# PKI for PHP
## Usage

Create CA Certificate: 
```
# Self sign RSA
./ppki cert:ca:new CARoot "CA Root"
# Self sign ECC
./ppki cert:ca:new CARootECC "ECC CA Root" --ecc
# Sign by CARoot
./ppki cert:ca:new CARoot2 "CA Root 2" --ca-name CARoot --days 180
```

Issue certificate: 
```
# Self sign RSA
./ppki cert:issue localhost localhost --dns-name localhost --ip-address 127.0.0.1
# Self sign ECC
./ppki cert:issue localhost localhost --dns-name localhost --ip-address 127.0.0.1 --ecc
# Sign by CARoot
./ppki cert:issue localhost localhost --ca-name CARoot --dns-name localhost --ip-address 127.0.0.1 --days 180
```

List Certificate:
```
# List CA certificates
./ppki cert:ca:list
# List Certificates
./ppki cert:list
```

Export Certificate:
```
# Export to stdout
./ppki cert:ca:export CARoot --fullchain --key
# Export to file
./ppki cert:export localhost --ca /etc/ssl/certs/localhost-ca.pem --concat /etc/ssl/private/localhost.pem
```

Delete Certificate:
```
# Delete CA Certificate
./ppki cert:ca:del CARoot
# Delete Certificate
./ppki cert:del localhost
```