<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\SuspiciousLogin\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;
use ReturnTypeWillChange;

/**
 * @method string getType()
 * @method void setType(string $type)
 * @method string getAppVersion()
 * @method void setAppVersion(string $version)
 * @method int getSamplesPositive()
 * @method void setSamplesPositive(int $samples)
 * @method int getSamplesShuffled()
 * @method void setSamplesShuffled(int $shuffled)
 * @method int getSamplesRandom()
 * @method void setSamplesRandom(int $samples)
 * @method int getEpochs()
 * @method void setEpochs(int $epochs)
 * @method int getLayers()
 * @method void setLayers(int $layers)
 * @method int getVectorDim()
 * @method void setVectorDim(int $dimensions)
 * @method float getLearningRate()
 * @method void setLearningRate(float $learningRate)
 * @method float getPrecisionY()
 * @method void setPrecisionY(float $precision)
 * @method float getPrecisionN()
 * @method void setPrecisionN(float $precision)
 * @method float getRecallY()
 * @method void setRecallY(float $recall)
 * @method float getRecallN()
 * @method void setRecallN(float $recall)
 * @method int getDuration()
 * @method void setDuration(int $layers)
 * @method int getCreatedAt()
 * @method void setCreatedAt(int $createdAt)
 * @method string getAddressType()
 * @method void setAddressType(string $type)
 */
class Model extends Entity implements JsonSerializable {
	protected $type;
	protected $appVersion;
	protected $samplesPositive;
	protected $samplesShuffled;
	protected $samplesRandom;
	protected $epochs;
	protected $layers;
	protected $vectorDim;
	protected $learningRate;
	protected $precisionY;
	protected $precisionN;
	protected $recallY;
	protected $recallN;
	protected $duration;
	protected $createdAt;
	protected $addressType;

	#[ReturnTypeWillChange]
	public function jsonSerialize(): array {
		return [
			'type' => $this->type,
			'appVersion' => $this->appVersion,
			'samplesPositive' => $this->samplesPositive,
			'samplesShuffled' => $this->samplesShuffled,
			'samplesRandom' => $this->samplesRandom,
			'epochs' => $this->epochs,
			'layers' => $this->layers,
			'vectorDim' => $this->vectorDim,
			'learningRate' => $this->learningRate,
			'precisionY' => $this->precisionY,
			'precisionN' => $this->precisionN,
			'recallY' => $this->recallY,
			'recallN' => $this->recallN,
			'duration' => $this->duration,
			'createdAt' => $this->createdAt,
			'addressType' => $this->addressType,
		];
	}
}
