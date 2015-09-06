VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  # box-config
  config.vm.box = "devops000"
  config.vm.box_url = "http://box.3wolt.de/devops000/"
  config.vm.box_check_update = true
  config.vm.box_version = "~> 1.0.6"

  # network-config
  config.vm.network "public_network", type: "dhcp"
  config.vm.boot_timeout = 600

  #config.vm.provider "virtualbox" do |v|
  #    v.gui = true
  #end

  # SSH-config
  config.ssh.username = "root"
  config.ssh.password = '\g}xr+e#p@g1'
  config.ssh.insert_key = true

  # hostname
  config.vm.hostname = "RedisStatus"

  # provisioners
  # ------------

  # nginx configs, copy and link
  config.vm.provision "file", source: "env/nginx/dist.conf", destination: "/etc/nginx/sites-available/dist"
  config.vm.provision "file", source: "env/vagrant/id_rsa", destination: "/root/.ssh/id_rsa"
  config.vm.provision "file", source: "env/vagrant/ssh_config", destination: "/root/.ssh/config"
  config.vm.provision "file", source: "env/vagrant/php/xdebug.ini", destination: "/etc/php5/mods-available/xdebug.ini"

  # shell commands
  config.vm.provision "shell", path: "env/vagrant/bootstrap.sh"
  
end
