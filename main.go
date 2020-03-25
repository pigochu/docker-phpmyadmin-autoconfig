package main

import (
	"context"
	"log"
	"os"
	"pigolab/docker-phpmyadmin-autoconfig/config"
	"time"

	"github.com/docker/docker/api/types"
	"github.com/docker/docker/api/types/events"
	"github.com/docker/docker/api/types/filters"
	"github.com/docker/docker/client"
)

var (
	autoconfigFile = "/etc/phpmyadmin/config.autoconfig.inc"
	instanceName   = os.Getenv("PHPMYADMIN_AUTOCONFIG_INSTANCE")
)

func main() {
	log.Print("docker-phpmyadmin-autoconfig started.")

	if 0 == len(instanceName) {
		instanceName = "phpmyadmin"
	}
	log.Print("Instance Name : ", instanceName)
	// wait for phpmyadmin init
	time.Sleep(time.Duration(2) * time.Second)
	generateConfig()

	// create empty setting to config.autoconfig.inc
	config.SyncDbConfig()

	// get running db container
	ctx := context.Background()
	cli, err := client.NewClientWithOpts(client.FromEnv, client.WithAPIVersionNegotiation())
	if err != nil {
		log.Fatal("Connect docker error : ", err)
	}
	runningFilterArgs, err := filters.FromJSON(`{
			"label": {
				"phpmyadmin.autoconfig.target": true
			},
			"status": {
				"running": true
			}
		}`)

	containers, err := cli.ContainerList(ctx, types.ContainerListOptions{Filters: runningFilterArgs})
	if err != nil {
		log.Fatal("Get container error :", err)
	}

	for _, container := range containers {
		var dbConfig = config.NewConfig(container)
		if dbConfig.Target == instanceName || dbConfig.Target == "*" {
			config.AddDbConfig(dbConfig)
		}
	}
	config.SyncDbConfig()

	// wait container's event
	filters := filters.NewArgs()
	filters.Add("label", "phpmyadmin.autoconfig.target")
	msgs, errs := cli.Events(ctx, types.EventsOptions{Filters: filters})

	// event cycle
	// start => *start
	// stop => kill *die stop
	// kill => kill *die
	// restart => kill *die stop *start restart
	// pause => *pause
	// resume => *unpause
	for {
		select {
		case err := <-errs:
			log.Fatal(err)
		case msg := <-msgs:
			log.Print("Container Action : ", msg.Action, ", ID: ", msg.Actor.ID)
			if msg.Action == "start" || msg.Action == "unpause" {
				config.AddDbConfig(config.NewConfig(msgToContainer(msg)))
				config.SyncDbConfig()
			} else if msg.Action == "pause" || msg.Action == "die" {
				config.RemoveDbConfig(msg.Actor.ID)
				config.SyncDbConfig()
			}
		}
	}
}

func msgToContainer(msg events.Message) types.Container {
	container := types.Container{ID: msg.ID, Labels: msg.Actor.Attributes}
	return container
}

func generateConfig() {

	var configFile = "/etc/phpmyadmin/config.inc.php"
	phpString := `/* Include autoconfig  */
	if (file_exists('/etc/phpmyadmin/config.autoconfig.inc.php')) {
		include('/etc/phpmyadmin/config.autoconfig.inc.php');
	}`

	f, err := os.OpenFile(configFile, os.O_APPEND|os.O_WRONLY, 0644)
	if err != nil {
		log.Fatal(err)
	}
	defer f.Close()
	if _, err := f.WriteString(phpString); err != nil {
		log.Fatal("Can not write to ", configFile, ", because ", err)
	}

}
