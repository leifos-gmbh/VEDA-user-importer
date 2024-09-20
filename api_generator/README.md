Generating/updating the api:
``` shell
java -jar openapi-generator-cli.jar generate -i 2024-03-11_veda_rest.json -g php -o out
rsync -avz --delete out/lib/ ../lib
rm -rf out
```
