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
                        # Asegurarse de que los permisos estén correctamente configurados
                        chown -R jenkins:jenkins "/var/jenkins_home/workspace/Bakend place"
                        chmod -R 775 "/var/jenkins_home/workspace/Bakend place"

                        # Verifica si Composer está disponible globalmente
                        if ! command -v composer > /dev/null; then
                            echo "❌ Composer no está instalado. Instalando Composer..."
                            # Descargar e instalar Composer si no está instalado
                            curl -sS https://getcomposer.org/installer | php
                            mv composer.phar /usr/local/bin/composer
                            echo "✅ Composer instalado con éxito"
                        else
                            echo "✅ Composer ya está instalado"
                        fi
                        
                        # Navegar al directorio correcto y ejecutar composer install
                        cd "reservasback"
                        composer install
                    '''

                }
            }
        }

        stage('Configurar Entorno Laravel') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    sh '''
                        # Asegúrate de copiar el archivo .env.example correctamente
                        cp "/var/jenkins_home/workspace/Bakend place/reservasback/.env.example" .env || echo ".env.example no encontrado"
                        
                        # Genera la clave de Laravel
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
