# ContentBridge — WordPress Plugin
## Useful WP-CLI commands for local testing environment during plugin development.
Creates a new WordPress post with title, content, publish status, and custom metadata (e.g., featured image and Yoast meta description).
``` bash
wp post create \
  --post_type=post \
  --post_title="Titolo del mio post 7" \
  --post_content="Questo è il contenuto del post" \
  --post_status=publish \
  --meta_input='{"_thumbnail_id":"12","_yoast_wpseo_metadesc":"Questa è la mia meta description personalizzata"}'
```
Updates an existing post (ID 123) by changing the title, content, status, and metadata.
``` bash
wp post update 123 \
  --post_title="Nuovo titolo del post" \
  --post_content="Nuovo contenuto del post" \
  --post_status=publish \
  --meta_input='{"_thumbnail_id":"21","_yoast_wpseo_metadesc":"Questa è la mia meta description personalizzata"}'
```

Sets up the WordPress testing environment by creating the test database and installing the specified WordPress version.
``` bash
./bin/install-wp-tests.sh wordpress_test root root "localhost:/tmp/mysql_socket/mysqld.sock" latest
```
## ContentBridge Inner Hooks
### Default Featured Image Override
Add this filter in your theme or plugin to override the default featured image used by ContentBridge. The hook forces the system to return the specified image ID as the new default.
``` php
add_filter(
    'writepoetry_contentbridge_default_featured_image',
    function () {
        return 21; // new default image ID.
    }
);
``` 

### Service Configuration Injection
Define external services, events, and payloads used by ContentBridge. Each key represents an integration. The filter must return an array of service definitions.
``` php
add_filter('writepoetry_contentbridge_service_config', function () {
    return [
        'service1' => [
            'url' => 'https://localhost/webhook/36f8013-4da4-4359-831b-8ab335b6524f',
            'secret' => '8Cp6Q8kYB8VeFZgwBdkSsOvJGarIq+LVlH/VrCkdimqXErDTFvIu552YDDqS+1V1pcGhBxZr341WFww70iudGw==',
            'events' => ['transition_post_status', 'post_updated'],
            'post_type' => ['post'],
            'payload' => [
                'type' => 'post'
            ]
        ],
        'service2' => [
            'url' => 'https:/api.test.com',
            'events' => ['post_updated'],
            'payload' => [
                'access_key' => 'RZwl0WLTRq9f6YTX57OvjjQRoRJCCbwMIWjOwmHspiI='
            ],
            'post_type' => ['post', 'page'],
            'timeout' => 30
        ],
    ];
});
```