pipeline {
    agent any

    environment {
        SONAR_TOKEN = credentials('sonarqube_token') // Define tu token en Jenkins > Credentials
    }

    stages {
        stage('Clonar Repositorio') {
            steps {
                git branch: 'main',
                    credentialsId: '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',
                    url: 'https://github.com/ROSAURA12345/TestWebcapa.git'
            }
        }

        stage('Instalar Dependencias') {
            steps {
                sh 'composer install'
            }
        }

        stage('Configurar Entorno') {
            steps {
                sh 'cp .env.example .env'
                sh 'php artisan key:generate'
            }
        }

        stage('Migrar Base de Datos') {
            steps {
                sh 'php artisan migrate --seed'
            }
        }

        stage('Ejecutar Pruebas') {
            steps {
                sh 'php artisan test'
            }
        }

        stage('Análisis con SonarQube') {
            steps {
                withSonarQubeEnv('sonarqube') {
                    sh """
                        sonar-scanner \
                        -Dsonar.projectKey=TestWebcapa \
                        -Dsonar.sources=app \
                        -Dsonar.php.coverage.reportPaths=storage/coverage.xml \
                        -Dsonar.host.url=http://localhost:9000 \
                        -Dsonar.login=$SONAR_TOKEN
                    """
                }
            }
        }

        stage('Quality Gate') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }
    }
}
