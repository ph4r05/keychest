#!/bin/bash
epiper supervisorctl restart 'keychest_p_db:*'
epiper supervisorctl restart 'keychest_p_db_emails:*'
epiper supervisorctl restart 'keychest_p_redis:*'
