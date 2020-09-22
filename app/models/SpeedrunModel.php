<?php

class SpeedrunModel
{
  private $db;

  public function __construct()
  {
    $this->db = new Database;
  }
  //Gets a random run to display for the homepage
  public function getRandomRun()
  {
    $sql = 'SELECT * FROM `runs` WHERE NOT `run_status` = "Unverified" ORDER BY RAND() LIMIT 1';
    $this->db->query($sql);
    return $this->db->single();
  }
  //Get run by its ID
  public function getRunByID($id)
  {
    $sql = 'SELECT * FROM `runs` WHERE `id` = :id';
    $this->db->query($sql);
    $this->db->bind('id', $id);
    return $this->db->single();
  }
  //Gets a disqualification for a run
  public function getDQReason($id)
  {
    $sql = 'SELECT * FROM `dq_reasons` WHERE `id` = :id';
    $this->db->query($sql);
    $this->db->bind('id', $id);
    return $this->db->single();
  }
  //Gets a list of most recent runs
  public function getRecentRuns($limit = 5)
  {
    $sql = "SELECT * FROM `runs` WHERE NOT `run_status` = 'Unverified' ORDER BY `creation_date` DESC LIMIT :lim";
    $this->db->query($sql);
    $this->db->bind('lim',$limit);
    return $this->db->resultSet();
  }
  //Gets a list of all unverified runs (for admins to verify/remove)
  public function getUnverifiedRuns()
  {
    $sql = "SELECT * FROM `runs` WHERE `run_status` = 'Unverified' ORDER BY `creation_date` DESC";
    $this->db->query($sql);
    return $this->db->resultSet();
  }
  //Gets a list of all runs uploaded by a runner using SESSION ID
  public function getRunsByID()
  {
    $sql = "SELECT * FROM `runs` WHERE `runner_id` = :id";
    $this->db->query($sql);
    $this->db->bind('id',$_SESSION['account']->id);
    return $this->db->resultSet();
  }
  //Submits a run to the server
  public function submitRun($data)
  {
    $sql = "INSERT INTO `runs` (`runner_id`, `platform`, `category`, `run_time`, `url`) VALUES (:runner_id,:platform,:category,:run_time,:url)";
    $this->db->query($sql);
    $this->db->bind('runner_id',$_SESSION['account']->id);
    $this->db->bind('platform',$data->platform);
    $this->db->bind('category',$data->category);
    $this->db->bind('run_time',$data->run_time);
    $this->db->bind('url',$data->url);
    $this->db->execute();
  }
  //Gets an array of categories
  public function getCategories()
  {
    $sql = "SHOW COLUMNS FROM `runs` WHERE FIELD LIKE 'category'";
    $this->db->query($sql);
    $res = $this->db->single()->Type;
    $res = str_replace("'", '',substr($res, 5, -2));
    return explode(',',$res);
  }
  //Gets an array of platforms
  public function getPlatforms()
  {
    $sql = "SHOW COLUMNS FROM `runs` WHERE FIELD LIKE 'platform'";
    $this->db->query($sql);
    $res = $this->db->single()->Type;
    $res = str_replace("'", '',substr($res, 5, -2));
    return explode(',',$res);
  }
  //Gets a run's placement on the entire site
  public function getGlobalPlacement($id)
  {
    $r = $this->getRunByID($id);
    $sql = "SELECT COUNT(`id`) FROM `runs` WHERE `run_status` = 'Verified' AND `run_time` < :t";
    $this->db->query($sql);
    $this->db->bind('t', $r->run_time);
    $res = get_object_vars($this->db->single())['COUNT(`id`)']+1;
    return $this->getPlacement($res);
  }
  //Deletes a speedrun by ID (Admin)
  public function deleteSpeedrun($id)
  {
    $sql = "DELETE FROM `runs` WHERE `id` = :id";
    $this->db->query($sql);
    $this->db->bind('id',$id);
    $this->db->execute();
    $this->deleteBlankDQs();
  }
  //Verifies a run by ID (Admin)
  public function verifyRun($id)
  {
    $sql = "UPDATE `runs` SET `run_status` = 'Verified' WHERE `id` = :id";
    $this->db->query($sql);
    $this->db->bind('id',$id);
    $this->db->execute();
  }
  //Deletes all run by runner ID using SESSION ID
  public function deleteMySpeedruns()
  {
    $sql = "DELETE FROM `runs` WHERE `runner_id` = :user_id";
    $this->db->query($sql);
    $this->db->bind('user_id',$_SESSION['account']->id);
    $this->db->execute();
    $this->deleteBlankDQs();
  }
  //Auto function, clears Disqualified reasons when no speedrun is attached
  public function deleteBlankDQs()
  {
    $sql = "DELETE FROM `dq_reasons`
            WHERE `id` NOT IN (SELECT f.id FROM `runs` f)";
    $this->db->query($sql);
    $this->db->execute();
  }
  //Gets a run's placement filtered by category
  public function getCategoryPlacement($id,$category)
  {
    $r = $this->getRunByID($id);
    $sql = "SELECT COUNT(`id`) FROM `runs` WHERE NOT `run_status` = 'Disqualified' AND `category` = :category AND `run_time` < :t";
    $this->db->query($sql);
    $this->db->bind('category',$category);
    $this->db->bind('t', $r->run_time);
    $res = get_object_vars($this->db->single())['COUNT(`id`)']+1;
    return $this->getPlacement($res);

  }
  //Takes a number and returns a numerical placement
  public function getPlacement($number)
  {
	   $placement_suffix=["th","st","nd","rd","th","th","th","th","th","th"];
		if($number % 100 >=20)
    {
			return ($number)."".$placement_suffix[ ($number) %10];
		}
		else if($number % 100 <=10)
    {
			return ($number)."".$placement_suffix[ ($number) %10];
		}
		else
    {
			return ($number)."".$placement_suffix[ 7 ];
		}
  }
  //Generates a leaderboard using filters
  public function getLeaderboard($category='', $runner='', $platform='', $offset=0)
  {
    if($category!='')
    {
      $category='%'.$category.'%';
    }
    if($platform!='')
    {
      $platform='%'.$platform.'%';
    }
    $sql = "SELECT * FROM `runs` WHERE `run_status` = 'Verified' ".($category!='' ? 'AND `category` LIKE :category ' : '').($runner!=''?'AND `runner_id` = :runner ':'').($platform!=''?'AND `platform` LIKE :platform ':'')."ORDER BY FIELD(`run_status`, 'Verified','Unverified','Disqualified'), `run_time`, `creation_date` LIMIT 200 OFFSET :off";
    $this->db->query($sql);
    $this->db->bind('off',$offset);
    //Binds additional search fields if available
    if($category!='')
    {
      $this->db->bind('category',$category);
    }
    if($runner!='')
    {
      $this->db->bind('runner',$runner);
    }
    if($platform!='')
    {
      $this->db->bind('platform',$platform);
    }
    $res = $this->db->resultSet();
    for ($i=0; $i < count($res); $i++)
    { //Iterates through, generating the placement for each run ordered by time, and rendering with a 1st, 2nd, and 3rd place badge
      $res[$i]->placement = $this->getPlacement($i+1);
      $res[$i]->placement_img = $res[$i]->placement=='1st' ? '<i class="fas fa-star text-warning"></i>' : '';
      $res[$i]->placement_img = $res[$i]->placement=='2nd' ? '<i class="fas fa-star-half-alt text-info"></i>' : $res[$i]->placement_img;
      $res[$i]->placement_img = $res[$i]->placement=='3rd' ? '<i class="far fa-star text-secondary"></i>' : $res[$i]->placement_img;
    }
    return $res;
  }
  //Gets a list of disqualified runs
  public function getWallOfShame($offset=0)
  {
    $sql = "SELECT * FROM `runs` WHERE `run_status` = 'Disqualified' LIMIT 200 OFFSET :off";
    $this->db->query($sql);
    $this->db->bind('off',$offset);
    $res = $this->db->resultSet();
    for ($i=0; $i < count($res); $i++)
    {
      $res[$i]->placement = 'N/A';
    }
    return $res;
  }
}
?>
