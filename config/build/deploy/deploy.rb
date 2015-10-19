# config valid only for Capistrano 3.1+
lock '>3.1'

set :application, 'project' # set your application name here
set :scm, :git
set :repo_url, '' # set your git repository URL here
set :user, "www-data"

set :stages, ["dev"]
set :default_stage, "dev"

# Default value for :format is :pretty
# set :format, :pretty

# Default value for :log_level is :debug
# set :log_level, :debug

# Default value for :pty is false
# set :pty, true

# Default value for :linked_files is []
set :linked_files, %w{config/parameters.yml}

# Default value for linked_dirs is []
set :linked_dirs, %w{logs}

# Default value for default_env is {}
# set :default_env, { path: "/opt/ruby/bin:$PATH" }

# Default value for keep_releases is 5
# set :keep_releases, 5


namespace :deploy do
    task :prepublish do
        on roles(:web) do
            execute "cd #{release_path} && BUILD_VERSION='#{release_timestamp}' CURRENT_PATH='#{release_path}' PREVIOUS_PATH='#{current_path}' make prepublish"
        end
    end

    task :postpublish do
        on roles(:web) do
            execute "cd #{release_path} && BUILD_VERSION='#{release_timestamp}' CURRENT_PATH='#{release_path}' PREVIOUS_PATH='#{current_path}' make postpublish"
        end
    end

    before :publishing, :prepublish
    after :publishing, :postpublish
end
