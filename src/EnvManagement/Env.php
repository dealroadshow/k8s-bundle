<?php

namespace Dealroadshow\Bundle\K8SBundle\EnvManagement;

class Env
{
    const DEV = 'dev';
    const QA = 'qa';
    const PRODUCTION = 'prod';
    const STAGING = 'staging';
    const TEST = 'test';

    // Pseudo env used in some classes like EnvAwareContainerMaker
    const DEFAULT = 'default';
}