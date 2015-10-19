set :state, :dev

# md-main server
server 'localhost', user: 'www-data', roles: :web
set :deploy_to, '/var/www/project/dev'
set :branch, 'develop'
