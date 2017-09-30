# world-spider-catalog
Harvesting the World Spider Catalog


## Linking names to references

The LSID XML doesn’t include the URL for the reference, but we can get these (it seems) like this:

```sql
SELECT * FROM names INNER JOIN `references` ON names.namePublishedIn = `references`.citation LIMIT 10;
```

## Mapping tasks

### Generate dump of names to match to ION

```
SELECT id, nameComplete, REPLACE(taxonAuthor, ",","") FROM names WHERE taxonAuthor IS NOT NULL and taxonAuthor <> “” LIMIT 100;
```

### Map to ION

```
SELECT worldspiders.id, names.id,  `worldspiders`.nameComplete, `worldspiders`.taxonAuthor FROM `worldspiders` JOIN names WHERE `worldspiders`.nameComplete = `names`.nameComplete AND `worldspiders`.taxonAuthor = `names`.taxonAuthor limit 100;
```
