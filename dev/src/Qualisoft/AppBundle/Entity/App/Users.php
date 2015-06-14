<?php

namespace Qualisoft\AppBundle\Entity\App;

use Doctrine\ORM\Mapping as ORM;

/**
 * Users
 */
class Users
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $userDocument;

    /**
     * @var string
     */
    private $userStatusCode;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $userMail;

    /**
     * @var string
     */
    private $userPass;

    /**
     * @var string
     */
    private $userLanguage;

    /**
     * @var integer
     */
    private $userDebugger;

    /**
     * @var string
     */
    private $userSecretquestion;

    /**
     * @var string
     */
    private $userSecretanswer;

    /**
     * @var \DateTime
     */
    private $userBirthday;

    /**
     * @var \DateTime
     */
    private $userLastactivation;

    /**
     * @var integer
     */
    private $userAlloweddays;

    /**
     * @var string
     */
    private $userPhoto;

    /**
     * @var string
     */
    private $userRoleCode;

    /**
     * @var string
     */
    private $userNotes;

    /**
     * @var \DateTime
     */
    private $userLastmovementdate;

    /**
     * @var string
     */
    private $userLastmovementip;

    /**
     * @var string
     */
    private $userLastmovementwho;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return Users
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userDocument
     *
     * @param string $userDocument
     * @return Users
     */
    public function setUserDocument($userDocument)
    {
        $this->userDocument = $userDocument;

        return $this;
    }

    /**
     * Get userDocument
     *
     * @return string 
     */
    public function getUserDocument()
    {
        return $this->userDocument;
    }

    /**
     * Set userStatusCode
     *
     * @param string $userStatusCode
     * @return Users
     */
    public function setUserStatusCode($userStatusCode)
    {
        $this->userStatusCode = $userStatusCode;

        return $this;
    }

    /**
     * Get userStatusCode
     *
     * @return string 
     */
    public function getUserStatusCode()
    {
        return $this->userStatusCode;
    }

    /**
     * Set userName
     *
     * @param string $userName
     * @return Users
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string 
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set userMail
     *
     * @param string $userMail
     * @return Users
     */
    public function setUserMail($userMail)
    {
        $this->userMail = $userMail;

        return $this;
    }

    /**
     * Get userMail
     *
     * @return string 
     */
    public function getUserMail()
    {
        return $this->userMail;
    }

    /**
     * Set userPass
     *
     * @param string $userPass
     * @return Users
     */
    public function setUserPass($userPass)
    {
        $this->userPass = $userPass;

        return $this;
    }

    /**
     * Get userPass
     *
     * @return string 
     */
    public function getUserPass()
    {
        return $this->userPass;
    }

    /**
     * Set userLanguage
     *
     * @param string $userLanguage
     * @return Users
     */
    public function setUserLanguage($userLanguage)
    {
        $this->userLanguage = $userLanguage;

        return $this;
    }

    /**
     * Get userLanguage
     *
     * @return string 
     */
    public function getUserLanguage()
    {
        return $this->userLanguage;
    }

    /**
     * Set userDebugger
     *
     * @param integer $userDebugger
     * @return Users
     */
    public function setUserDebugger($userDebugger)
    {
        $this->userDebugger = $userDebugger;

        return $this;
    }

    /**
     * Get userDebugger
     *
     * @return integer 
     */
    public function getUserDebugger()
    {
        return $this->userDebugger;
    }

    /**
     * Set userSecretquestion
     *
     * @param string $userSecretquestion
     * @return Users
     */
    public function setUserSecretquestion($userSecretquestion)
    {
        $this->userSecretquestion = $userSecretquestion;

        return $this;
    }

    /**
     * Get userSecretquestion
     *
     * @return string 
     */
    public function getUserSecretquestion()
    {
        return $this->userSecretquestion;
    }

    /**
     * Set userSecretanswer
     *
     * @param string $userSecretanswer
     * @return Users
     */
    public function setUserSecretanswer($userSecretanswer)
    {
        $this->userSecretanswer = $userSecretanswer;

        return $this;
    }

    /**
     * Get userSecretanswer
     *
     * @return string 
     */
    public function getUserSecretanswer()
    {
        return $this->userSecretanswer;
    }

    /**
     * Set userBirthday
     *
     * @param \DateTime $userBirthday
     * @return Users
     */
    public function setUserBirthday($userBirthday)
    {
        $this->userBirthday = $userBirthday;

        return $this;
    }

    /**
     * Get userBirthday
     *
     * @return \DateTime 
     */
    public function getUserBirthday()
    {
        return $this->userBirthday;
    }

    /**
     * Set userLastactivation
     *
     * @param \DateTime $userLastactivation
     * @return Users
     */
    public function setUserLastactivation($userLastactivation)
    {
        $this->userLastactivation = $userLastactivation;

        return $this;
    }

    /**
     * Get userLastactivation
     *
     * @return \DateTime 
     */
    public function getUserLastactivation()
    {
        return $this->userLastactivation;
    }

    /**
     * Set userAlloweddays
     *
     * @param integer $userAlloweddays
     * @return Users
     */
    public function setUserAlloweddays($userAlloweddays)
    {
        $this->userAlloweddays = $userAlloweddays;

        return $this;
    }

    /**
     * Get userAlloweddays
     *
     * @return integer 
     */
    public function getUserAlloweddays()
    {
        return $this->userAlloweddays;
    }

    /**
     * Set userPhoto
     *
     * @param string $userPhoto
     * @return Users
     */
    public function setUserPhoto($userPhoto)
    {
        $this->userPhoto = $userPhoto;

        return $this;
    }

    /**
     * Get userPhoto
     *
     * @return string 
     */
    public function getUserPhoto()
    {
        return $this->userPhoto;
    }

    /**
     * Set userRoleCode
     *
     * @param string $userRoleCode
     * @return Users
     */
    public function setUserRoleCode($userRoleCode)
    {
        $this->userRoleCode = $userRoleCode;

        return $this;
    }

    /**
     * Get userRoleCode
     *
     * @return string 
     */
    public function getUserRoleCode()
    {
        return $this->userRoleCode;
    }

    /**
     * Set userNotes
     *
     * @param string $userNotes
     * @return Users
     */
    public function setUserNotes($userNotes)
    {
        $this->userNotes = $userNotes;

        return $this;
    }

    /**
     * Get userNotes
     *
     * @return string 
     */
    public function getUserNotes()
    {
        return $this->userNotes;
    }

    /**
     * Set userLastmovementdate
     *
     * @param \DateTime $userLastmovementdate
     * @return Users
     */
    public function setUserLastmovementdate($userLastmovementdate)
    {
        $this->userLastmovementdate = $userLastmovementdate;

        return $this;
    }

    /**
     * Get userLastmovementdate
     *
     * @return \DateTime 
     */
    public function getUserLastmovementdate()
    {
        return $this->userLastmovementdate;
    }

    /**
     * Set userLastmovementip
     *
     * @param string $userLastmovementip
     * @return Users
     */
    public function setUserLastmovementip($userLastmovementip)
    {
        $this->userLastmovementip = $userLastmovementip;

        return $this;
    }

    /**
     * Get userLastmovementip
     *
     * @return string 
     */
    public function getUserLastmovementip()
    {
        return $this->userLastmovementip;
    }

    /**
     * Set userLastmovementwho
     *
     * @param string $userLastmovementwho
     * @return Users
     */
    public function setUserLastmovementwho($userLastmovementwho)
    {
        $this->userLastmovementwho = $userLastmovementwho;

        return $this;
    }

    /**
     * Get userLastmovementwho
     *
     * @return string 
     */
    public function getUserLastmovementwho()
    {
        return $this->userLastmovementwho;
    }
}
