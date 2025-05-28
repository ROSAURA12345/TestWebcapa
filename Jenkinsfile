pipeline {
    agent any

    environment {
        SONAR_TOKEN = credentials('Sonarqube') // ID del token en Jenkins > Credentials
    }

    stages {
        stage('Clonar Repositorio') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    git branch: 'main',
                        credentialsId: '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', // Reemplaza con tu ID real
                        url: 'https://github.com/ROSAURA12345/TestWebcapa.git'
                }
            }
        }

        stage('Instalar Dependencias con Composer') {
            steps {
                timeout(time: 8, unit: 'MINUTES') {
                    sh '''
                        cd reservasback
                        composer install
                    '''
                }
            }
        }

        stage('Configurar Entorno Laravel') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    sh '''
                        cd reservasback

                        if [ ! -f .env ]; then
                          cp .env.example .env
                        fi

                        php artisan key:generate
                    '''
                }
            }
        }

        stage('Migrar y Poblar Base de Datos') {
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    sh '''
                        cd reservasback
                        php artisan config:clear
                        php artisan migrate:fresh --seed || echo "Migración falló, puede que ya esté aplicada"
                    '''
                }
            }
        }

        stage('Ejecutar Pruebas y Generar Cobertura') {
            steps {
                timeout(time: 5, unit: 'MINUTES') {
                    sh '''
                        cd reservasback
                        ./vendor/bin/phpunit --coverage-clover storage/coverage.xml
                    '''
                }
            }
        }

        stage('Análisis con SonarQube') {
            steps {
                timeout(time: 4, unit: 'MINUTES') {
                    withSonarQubeEnv('sonarqube') {
                        sh '''
                            cd reservasback
                            sonar-scanner \
                              -Dsonar.projectKey=TestWebcapa \
                              -Dsonar.sources=app \
                              -Dsonar.host.url=http://docker.sonar:9000 \
                              -Dsonar.php.coverage.reportPaths=storage/coverage.xml \
                              -Dsonar.login=$SONAR_TOKEN
                        '''
                    }
                }
            }
        }

        stage('Quality Gate') {
            steps {
                sleep(10) // da tiempo a Sonar para procesar
                timeout(time: 2, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
                }
            }
        }

        stage('Despliegue (Opcional)') {
            steps {
                timeout(time: 3, unit: 'MINUTES') {
                    echo 'Simulación de despliegue completada. Puedes integrar con Forge, Docker u otro.'
                }
            }
        }
    }

    post {
        success {
            echo '✅ Pipeline ejecutado con éxito.'
        }
        failure {
            echo '❌ Error en alguna etapa del pipeline.'
        }
    }
}
