# world-spider-catalog
Harvesting the World Spider Catalog


## Linking names to references

The LSID XML doesn’t include the URL for the reference, but we can get these (it seems) like this:

```sql
SELECT * FROM names INNER JOIN `references` ON names.namePublishedIn = `references`.citation LIMIT 10;
```

