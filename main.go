package main

import (
	"context"
	"log"
	"os"
	"pigolab/docker-phpmyadmin-autoconfig/config"

	"github.com/docker/docker/api/types"
	"github.com/docker/docker/api/types/events"
	"github.com/docker/docker/api/types/filters"
	"github.com/docker/docker/client"
)

var (
	autoconfigFile = "/etc/phpmyadmin/phpmyadmin.autoconfig.php"
	instanceName   = os.Getenv("PHPMYADMIN_AUTOCONFIG_INSTANCE")
)

func main() {
	log.Print("docker-phpmyadmin-autoconfig started.")

	if 0 == len(instanceName) {
		instanceName = "phpmyadmin"
	}
	log.Print("Instance Name : ", instanceName)

	// create empty setting to phpmyadmin.autoconfig.php
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
	// stop => *kill die stop
	// kill => *kill die
	// restart => *kill die stop *start restart
	// pause => *pause
	// resume => *unpause
	for {
		select {
		case err := <-errs:
			log.Fatal(err)
		case msg := <-msgs:
			if msg.Action == "start" || msg.Action == "unpause" {
				config.AddDbConfig(config.NewConfig(msgToContainer(msg)))
				config.SyncDbConfig()
			} else if msg.Action == "pause" || msg.Action == "kill" {
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
