# Integration Tests

Integration tests go here. They require real Zendesk credentials and a
running Zendesk sandbox — they are intentionally excluded from the default
`phpunit.xml` test run.

To run them:

```bash
# Set credentials first
export ZENDESK_SUBDOMAIN=your-sandbox
export ZENDESK_USERNAME=agent@yourcompany.com
export ZENDESK_TOKEN=your_token

./vendor/bin/phpunit --testsuite Integration
```
