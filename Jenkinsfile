#!/usr/bin/env groovy

pipeline {
    agent any

    stages {
        stage('Build') {
            steps {
                echo 'Building...'
            }
        }

        stage('Test: automated code tests') {
            steps {
                echo 'Running automated testing...'
            }
        }

        stage('Package'){
            steps {
                echo 'Packaging application into images and pushing to docker hub...'
            }
        }

        stage('Deploy: staging') {
            steps {
                echo 'Deploying to staging environment...'
            }
        }

        stage('Test: staging') {
            steps {
                echo 'App on staging waiting for approval...'
            }
        }

        state('Deploy: production'){
            steps {
                echo 'Deploying to production environment...'
            }
        }

        stage('Test: production'){
            steps {
                echo 'Final checks to make sure everything is working good in prod...'
            }
        }
    }
}