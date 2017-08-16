#!/bin/bash
epiper supervisorctl stop 'keychest_p_db:*'
epiper supervisorctl stop 'keychest_p_db_emails:*'
epiper supervisorctl stop 'keychest_p_redis:*'
