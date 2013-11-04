<?php

function generate_token() {

	return MD5(uniqid());

}