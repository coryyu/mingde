#!/bin/bash

if [ "$1" == "release" ]; then
    branch='master'
    else
        branch='develop'
        fi

        /usr/bin/git checkout $branch 2>&1
        /usr/bin/git stash 2>&1
        /usr/bin/git pull 2>&1
