# plugin_topx for Cacti

## TopX
Displays hights and lows (example: most utilization CPU, interface traffic, ..)
~~Topx allows you to add an arithmetic operation or combine data source (example: discard and error traffic together, ..)~~

## Author
Petr Macek (petr.macek@kostax.cz)

## Screenshot
![topx_0 4](https://user-images.githubusercontent.com/26485719/33798513-1a7a4dba-dd1a-11e7-8ffe-f7f76c5124ba.png)

## Installation
Copy directory topx to plugins directory  
Check file permission (Linux/unix - readable for www server)  
Enable plugin (Console -> Plugin management)   
    
## Upgrade    
Copy and rewrite files  
Check file permission (Linux/unix - readable for www server)  
Disable and deinstall old version (Console -> Plugin management)   
Install and enable new version (Console -> Plugin management)   
    
## Possible Bugs or any ideas?
If you find a problem, let me know via github or https://forums.cacti.net/viewtopic.php?f=5&t=56129
   

## Changelog
	--- 0.5
		Add theme support
		Remove my data source poller code, using native dsstats
		Remove arithmetical operation (dsstats doesn't support this)
		 
 	--- 0.4
		Add chartJS graphs
		Add human readable values 
		Fix empty source

	--- 0.3
		rewrite for cacti 1.x

	--- 0.2
		Fixed error - logging too much
	
	--- 0.1
		Beginning


