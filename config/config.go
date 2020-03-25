package config

import (
	"io/ioutil"
	"log"
	"regexp"
	"strings"
	"sync"

	"github.com/docker/docker/api/types"
)

type DbConfig struct {
	ContainerID string
	Target      string
	Cfg         map[string]string
}

var (
	autoconfigFile = "/etc/phpmyadmin/phpmyadmin.autoconfig.php"
	dbconfigs      = make(map[string]*DbConfig)
	mu             sync.Mutex
)

// NewConfig :Create config from docker's Container type
func NewConfig(container types.Container) *DbConfig {

	var dbConfig = &DbConfig{ContainerID: container.ID, Cfg: make(map[string]string)}
	r := regexp.MustCompile(`phpmyadmin\.autoconfig\.cfg\.(.*)`)
	// Parse phpmyadmin.autoconfig... environments
	for labelKey, labelValue := range container.Labels {
		match := r.FindStringSubmatch(labelKey)
		if len(match) > 1 {

			caseValue := strings.ToLower(labelValue)
			if caseValue == "true" || caseValue == "false" {
				dbConfig.Cfg[match[1]] = labelValue
			} else {
				dbConfig.Cfg[match[1]] = "\"" + labelValue + "\""
			}

		} else if labelKey == "phpmyadmin.autoconfig.target" {
			dbConfig.Target = labelValue
		}
	}
	return dbConfig
}

func AddDbConfig(config *DbConfig) {
	mu.Lock()
	dbconfigs[config.ContainerID] = config
	log.Print("Add container ID:", config.ContainerID, " to config.")
	mu.Unlock()
}

func RemoveDbConfig(containerID string) {
	mu.Lock()
	delete(dbconfigs, containerID)
	log.Print("Remove container ID:", containerID, " from config.")
	mu.Unlock()
}

// SyncDbConfig write config to phpmyadmin
func SyncDbConfig() {
	mu.Lock()
	defer mu.Unlock()
	var phpString = `<?php
	if(!isset($i)) {
		$i=0;
	}
`

	for _, v := range dbconfigs {

		phpString +=
			`
	$cfg[$i]["host"]="` + v.ContainerID[0:12] + `";`

		for cfgKey, cfgValue := range v.Cfg {
			phpString +=
				`
	$cfg[$i]["` + cfgKey + `"]=` + cfgValue + `;`
		}
		phpString += `
	$i++;
`
	}

	err := ioutil.WriteFile(autoconfigFile, []byte(phpString), 0644)
	if err != nil {
		log.Fatal("Can not write to ", autoconfigFile, ", because ", err)
	}
}
