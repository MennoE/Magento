<?php

class Kega_Eprofiler
{
	/**
	 * Create or load a profile on given criteria
	 * 
	 * @param array $criteria
	 * @return Kega_Eprofiler_Profile
	 */
    public function getProfile($criteria=array())
    {
    	return new Kega_Eprofiler_Profile($criteria);
    }
    
}