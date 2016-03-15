<?php
namespace Producer\Api;

interface ApiInterface
{
    public function getRepo();
    public function fetchIssues();
}
