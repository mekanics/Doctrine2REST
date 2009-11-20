<?php

namespace DoctrineExtensions\REST\Action;

interface ActionInterface
{
    function validate();
    function execute();
}