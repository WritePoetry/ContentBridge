``` bash
wp post create \
  --post_type=post \
  --post_title="Titolo del mio post 7" \
  --post_content="Questo è il contenuto del post" \
  --post_status=publish \
  --meta_input='{"_thumbnail_id":"2929","_yoast_wpseo_metadesc":"Questa è la mia meta description personalizzata"}'
```

``` bash
wp post update 123 \
  --post_title="Nuovo titolo del post" \
  --post_content="Nuovo contenuto del post" \
  --post_status=publish \
  --meta_input='{"_thumbnail_id":"21","_yoast_wpseo_metadesc":"Questa è la mia meta description personalizzata"}'
```


``` bash
./bin/install-wp-tests.sh wordpress_test root root "localhost:/tmp/mysql_socket/mysqld.sock" latest
```