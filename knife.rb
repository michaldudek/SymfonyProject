cookbook_path    [".cookbooks"]
node_path        "config/build/deploy/nodes"
role_path        "config/build/deploy/roles"
environment_path "config/build/deploy/environments"
data_bag_path    "config/build/deploy/data_bags"
#encrypted_data_bag_secret "data_bag_key"

knife[:berkshelf_path] = ".cookbooks"
knife[:bootstrap_version] = "12.5.1"