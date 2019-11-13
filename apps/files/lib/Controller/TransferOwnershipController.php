<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Files\Controller;

use OCA\Files\BackgroundJob\TransferOwnership;
use OCA\Files\Db\TransferOwnershipMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\OCSController;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJobList;
use OCP\IRequest;
use OCP\Notification\IManager as NotificationManager;

class TransferOwnershipController extends OCSController {

	/** @var string */
	private $userId;
	/** @var NotificationManager */
	private $notificationManager;
	/** @var ITimeFactory */
	private $timeFactory;
	/** @var IJobList */
	private $jobList;
	/** @var TransferOwnershipMapper */
	private $mapper;

	public function __construct(string $appName,
								IRequest $request,
								string $userId,
								NotificationManager $notificationManager,
								ITimeFactory $timeFactory,
								IJobList $jobList,
								TransferOwnershipMapper $mapper) {
		parent::__construct($appName, $request);

		$this->userId = $userId;
		$this->notificationManager = $notificationManager;
		$this->timeFactory = $timeFactory;
		$this->jobList = $jobList;
		$this->mapper = $mapper;
	}


	/**
	 * @NoAdminRequired
	 *
	 * TODO: more checks
	 */
	public function transfer(string $recipient, string $path): DataResponse {

		//TODO: verify if recipient exists
		//TODO: verify if path exists

		$transferOwnership = new \OCA\Files\Db\TransferOwnership();
		$transferOwnership->setSourceUser($this->userId);
		$transferOwnership->setTargetUser($recipient);
		$transferOwnership->setPath($path);
		$transferOwnership = $this->mapper->insert($transferOwnership);

		$notification = $this->notificationManager->createNotification();
		$notification->setUser($recipient)
			->setApp('files')
			->setDateTime($this->timeFactory->getDateTime())
			->setSubject('transferownershipRequest', [
				'sourceUser' => $this->userId,
				'targetUser' => $recipient,
				'path' => $path
			])
			->setObject('transfer', (string)$transferOwnership->getId());

		$this->notificationManager->notify($notification);

		return new DataResponse([]);
	}

	/**
	 * @NoAdminRequired
	 */
	public function accept(string $id): DataResponse {
		$transferId = (int)$id;

		try {
			$transferOwnership = $this->mapper->getById($transferId);
		} catch (DoesNotExistException $e) {
			return new DataResponse([], Http::STATUS_NOT_FOUND);
		}

		if ($transferOwnership->getTargetUser() !== $this->userId) {
			return new DataResponse([], Http::STATUS_FORBIDDEN);
		}

		$this->jobList->add(TransferOwnership::class, [
			'source-user' => $transferOwnership->getSourceUser(),
			'destination-user' => $transferOwnership->getTargetUser(),
			'path' => $transferOwnership->getPath(),
		]);

		$notification = $this->notificationManager->createNotification();
		$notification->setApp('files')
			->setObject('transfer', $id);
		$this->notificationManager->markProcessed($notification);

		return new DataResponse([], Http::STATUS_OK);
	}

	public function reject(string $id): DataResponse {
		// TODO: Mark notification as done
		// TODO: Send notification to initiator of rejection
	}

}
