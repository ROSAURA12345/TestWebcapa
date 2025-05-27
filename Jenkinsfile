pipeline {
    agent any

    environment {
        SONAR_TOKEN = credentials('Sonarqube') // Este ID debe existir en Jenkins > Credentials
    }


    stages {
        stage('Clonar Repositorio') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    git branch: 'main',
                        credentialsId: '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', // Reemplaza con tu ID registrado
                        url: 'https://github.com/ROSAURA12345/TestWebcapa.git'
                }
            }
        }

        stage('Verificar Composer e Instalar Dependencias') {
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    sh '''
                        if ! command -v composer > /dev/null; then
                            echo "❌ Composer no está instalado. Abortando pipeline."
                            exit 1
                        fi

                        echo "✅ Composer está instalado"
                        composer install
                    '''
                }
            }
        }

        stage('Configurar Entorno Laravel') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    sh '''
                        cp .env.example .env || echo ".env.example no encontrado"
                        php artisan key:generate
                    '''
                }
            }
        }

        stage('Migrar y Poblar Base de Datos') {
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    sh 'php artisan migrate --seed'
                }
            }
        }

        stage('Ejecutar Pruebas') {
            steps {
                timeout(time: 4, unit: 'MINUTES') {
                    sh 'php artisan test'
                }
            }
        }

        stage('Análisis con SonarQube') {
            steps {
                timeout(time: 4, unit: 'MINUTES') {
                    withSonarQubeEnv('sonarqube') {
                        sh '''
                            sonar-scanner \
                            -Dsonar.projectKey=TestWebcapa \
                            -Dsonar.sources=app \
                            -Dsonar.php.coverage.reportPaths=storage/coverage.xml \
                            -Dsonar.host.url=http://localhost:9000 \
                            -Dsonar.login=$SONAR_TOKEN
                        '''
                    }
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

        stage('Deploy (Opcional)') {
            when {
                expression { return false } // Cambia a true si deseas activarlo
            }
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    echo 'Simulando despliegue de Laravel...'
                    // Si necesitas servir Laravel (no recomendado en producción):
                    // sh 'php artisan serve --host=0.0.0.0 --port=8000 &'
                }
            }
        }
    }

    post {
        failure {
            echo '❌ El pipeline falló. Revisa las etapas anteriores.'
        }
        success {
            echo '✅ Pipeline completado con éxito.'
        }
    }
}
