<?php

namespace Iamczech\EasyAdminFieldsBundle\Interface;

interface TreeInterface
{
    public function getRoot();
    public function getLevel();
    public function getLeft();
    public function getRight();
    public function getParent();
    public function getChildren();
    public function getSequence();
}
