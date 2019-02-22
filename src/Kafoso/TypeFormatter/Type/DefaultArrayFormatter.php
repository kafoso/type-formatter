<?php
declare(strict_types = 1);

namespace Kafoso\TypeFormatter\Type;

use Kafoso\TypeFormatter\TypeFormatter;
use Kafoso\TypeFormatter\Abstraction\Type\AbstractFormatter;

class DefaultArrayFormatter extends AbstractFormatter implements ArrayFormatterInterface
{
    /**
     * @inheritDoc
     */
    public function format(array $array): ?string
    {
        $arraySampleSize = max(0, $this->getTypeFormatter()->getArraySampleSize());
        $depthCurrent = $this->getTypeFormatter()->getArrayDepthCurrent();
        $depthMaximum = max(0, $this->getTypeFormatter()->getArrayDepthMaximum());
        $return = "";
        $isSample = false;
        if ($arraySampleSize > 0) {
            $segments = [];
            $index = 1;
            $subTypeFormatter = clone $this->getTypeFormatter();
            $subTypeFormatter = $subTypeFormatter->withArrayDepthCurrent($depthCurrent + 1);
            $subTypeFormatter = $subTypeFormatter->withObjectDepthCurrent(
                $this->getTypeFormatter()->getObjectDepthCurrent()+1
            );
            foreach ($array as $k => $v) {
                $segment = "";
                if ($this->isPrependingType()) {
                    $segment = $this->getTypeFormatter()->typeCast($k, false);
                } else {
                    $segment = $this->getTypeFormatter()->cast($k, false);
                }
                $segment .= " => ";
                if (is_array($v)) {
                    if ($depthCurrent >= $depthMaximum) {
                        if ($this->isPrependingType()) {
                            $segment .= $subTypeFormatter->typeCast($v, $this->isSamplifying());
                        } else {
                            $segment .= $subTypeFormatter->cast($v, $this->isSamplifying());
                        }
                    } else {
                        $segment .= sprintf(
                            "[%s]",
                            TypeFormatter::SAMPLE_ELLIPSIS
                        );
                    }
                } else {
                    if ($this->isPrependingType()) {
                        $segment .= $this->getTypeFormatter()->typeCast($v, $this->isSamplifying());
                    } else {
                        $segment .= $this->getTypeFormatter()->cast($v, $this->isSamplifying());
                    }
                }
                $segments[] = $segment;
                if ($index >= $arraySampleSize) {
                    break;
                }
                $index++;
            }
            $return = "[" . implode(", ", $segments);
            $surplusCount = (count($array) - $arraySampleSize);
            $isSample = ($surplusCount > 0);
            if ($isSample) {
                $return .= sprintf(
                    ", %s and %d more %s",
                    TypeFormatter::SAMPLE_ELLIPSIS,
                    $surplusCount,
                    (1 === $surplusCount ? "element" : "elements")
                );
            }
            $return .= "]";
        } else {
            $return = sprintf("[%s]", TypeFormatter::SAMPLE_ELLIPSIS);
            $isSample = true;
        }
        if ($this->isPrependingType()) {
            $return = sprintf(
                "(array(%d)) %s",
                count($array),
                $return
            );
        }
        if ($isSample) {
            $return .= " (sample)";
        }
        return $return;
    }

    public function formatEnsureString(array $array): string
    {
        return strval($this->format($array));
    }
}
