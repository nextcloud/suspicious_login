# Statistics

A collection of useful SQL queries to get some stats from the data in the database. Tested with MySQL.

## Suspicious login attempts

```sql
SELECT Year(From_unixtime(created_at)) AS year,
       Week(From_unixtime(created_at)) AS week,
       Count(*)                        AS cnt
FROM   oc_suspicious_login
GROUP  BY year,
          week
ORDER  BY year,
          week;  
```

## Number of notifications sent per week

```sql
SELECT Year(From_unixtime(created_at)) AS year,
       Week(From_unixtime(created_at)) AS week,
       Count(*)                        AS cnt
FROM   oc_suspicious_login
WHERE  notification_state = 1
GROUP  BY year,
          week
ORDER  BY year,
          week;  
```

## IPv4 vs IPv6 distribution in suspicious login attempts

```sql
SELECT (SELECT SUM(seen)
        FROM   oc_login_ips_aggregated
        WHERE  ip LIKE '%.%.%.%') / (SELECT SUM(seen)
                                     FROM   oc_login_ips_aggregated)     AS
       pct_v4,
       (SELECT SUM(seen)
        FROM   oc_login_ips_aggregated
        WHERE  ip NOT LIKE '%.%.%.%') / (SELECT SUM(seen)
                                         FROM   oc_login_ips_aggregated) AS
       pct_v6
```
