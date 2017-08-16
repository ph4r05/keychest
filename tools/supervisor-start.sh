#!/bin/bash
epiper supervisorctl start 'keychest_p_db:*'
epiper supervisorctl start 'keychest_p_db_emails:*'
epiper supervisorctl start 'keychest_p_redis:*'
