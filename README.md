#CRYBOARD

[![CircleCI](https://circleci.com/bb/vperillat/cryboard.svg?style=shield&circle-token=1375da6d6b571a85451dd257bd5318f8ec4352f4)](https://circleci.com/bb/vperillat/cryboard)

Cryboard is a board which display markets information about some cryptocurrencies.
It will aggregate data from exchanges and website like coinmarketcap and display it in one place.
It's a good place to look current price of your favorite currency and to cry as you see it's so damn low everywhere.

## Installation
You need 
* Git
* Vagrant 
* VirtualBox

##### Step 1 :
    `vagrant up`

##### Step 2 :
    `vagrant ssh`

##### Step 3 :
    `cd cryboard & php /bin/composer.phar install`
    
I recommend you to install ansible in the vagrant box. Then you'll be able to run the following command. This will set up cron task which will help you to sync exchange data to the different coins. (Don't do it inside the vagrant. Won't work without API KEY)

    `ansible-playbook playbook/cron.yml`


Issue about Vagrant and Virtualbox shared folder ?
    
    `sudo ln -sf /usr/lib/x86_64-linux-gnu/VBoxGuestAdditions/mount.vboxsf /sbin/mount.vboxsf̀`

Possible issue about RSYNC. Check the src path. Fix it for your case.

_**that's all folks**_
