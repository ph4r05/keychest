#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "Fixing: $DIR"
chown -R nginx:ec2-user $DIR
chmod -R g+rw $DIR
