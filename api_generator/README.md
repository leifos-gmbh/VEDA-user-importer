Generating/updating the api:
``` shell
java -jar openapi-generator-cli.jar generate -i 2023-08-03_veda_rest.json -g php -o out
rsync -avz out/lib/ ../lib
rm -rf out
```