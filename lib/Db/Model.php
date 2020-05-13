<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 */

namespace OCA\SuspiciousLogin\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

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

	public function jsonSerialize() {
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
