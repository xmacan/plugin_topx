# plugin_topx for Cacti

## TopX
Topx allows you to add an arithmetic operation or combine data source (example: discard and error traffic together, ..) and 
displays hights and lows (example: most utilization CPU, interface traffic, ..)

## Author
Petr Macek (petr.macek@kostax.cz)

## Screenshot
![topx_0 4](https://user-images.githubusercontent.com/26485719/33798484-77f3a780-dd19-11e7-9cfc-f9b79704c74a.png)

## Installation
Copy directory topx to plugins directory
Check file permission (Linux/unix - readable for www server)
Enable plugin (Console -> Plugin management
Wait at least 3 poller cycles for result
    
## Upgrade    
Copy and rewrite files
Check file permission (Linux/unix - readable for www server)
If you want to preserve old data you must comment all in  plugin_topx_uninstall function in setup.php!
Disable and deinstall old version (Console -> Plugin management) 
Install and enable new version (Console -> Plugin management) 
    
## Possible Bugs or any ideas?
If you find a problem, let me know via github or https://forums.cacti.net/viewtopic.php?f=5&t=56129
   

## Changelog
	--- 0.3
		rewrite for cacti 1.x

	--- 0.2
		Fixed error - logging too much
	
	--- 0.1
		Beginning


